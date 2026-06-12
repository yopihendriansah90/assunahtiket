<?php

namespace App\Http\Controllers;

use App\Models\Checkin;
use App\Models\EventGate;
use App\Models\ScanAttempt;
use App\Models\Student;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Str;

class GateAuthController extends Controller
{
    public function showLogin(Request $request): View|RedirectResponse
    {
        $user = Auth::user();

        if ($user?->canAccessGateDashboard()) {
            return redirect()->route('gate.dashboard');
        }

        if ($user) {
            return redirect('/admin');
        }

        return view('gate.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'Email atau password tidak valid.',
            ]);
        }

        $request->session()->regenerate();

        $user = $request->user();

        if (! $user || ! $user->canAccessGateDashboard()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => 'Akun ini tidak memiliki akses ke dashboard gate.',
            ]);
        }

        return redirect()->route('gate.dashboard');
    }

    public function dashboard(Request $request): View
    {
        $user = $request->user();

        $gatesQuery = $this->accessibleGatesQuery($user);

        $gates = $gatesQuery->get();
        $selectedGate = null;
        $scanResult = $request->session()->get('gate.scan_result');

        if ($gates->isNotEmpty()) {
            $selectedGateId = $request->integer('gate');
            $selectedGate = $gates->firstWhere('id', $selectedGateId) ?? $gates->first();
        }

        if (is_array($scanResult) && filled($scanResult['ticket_id'] ?? null)) {
            $scanResult['ticket'] = Ticket::query()
                ->with(['student.eventClass', 'event', 'latestCheckin'])
                ->find($scanResult['ticket_id']);

            if (filled($scanResult['checkin_id'] ?? null)) {
                $scanResult['checkin'] = Checkin::query()->find($scanResult['checkin_id']);
            }
        }

        $gateStats = $this->buildGateStats($selectedGate);
        $recentScans = $this->formatRecentScans($this->buildRecentScans($selectedGate));

        return view('gate.dashboard', [
            'user' => $user,
            'gates' => $gates,
            'selectedGate' => $selectedGate,
            'scanResult' => $scanResult,
            'gateStats' => $gateStats,
            'recentScans' => $recentScans,
        ]);
    }

    public function scan(Request $request): RedirectResponse|JsonResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'gate_id' => ['required', 'integer'],
            'q' => ['required', 'string'],
        ]);

        $gate = $this->accessibleGatesQuery($user)
            ->whereKey($data['gate_id'])
            ->firstOrFail();

        $rawQuery = trim($data['q']);
        $normalizedQuery = Str::of($rawQuery)
            ->ascii()
            ->trim()
            ->replaceMatches('/\s+/', '')
            ->toString();

        $ticket = Ticket::query()
            ->with(['student.eventClass', 'event', 'latestCheckin'])
            ->where('event_id', $gate->event_id)
            ->where(function (Builder $query) use ($rawQuery, $normalizedQuery): void {
                $query
                    ->whereRaw('LOWER(ticket_code) = ?', [mb_strtolower($normalizedQuery)])
                    ->orWhereRaw('LOWER(qr_token) = ?', [mb_strtolower($normalizedQuery)])
                    ->orWhereRaw('LOWER(ticket_code) = ?', [mb_strtolower($rawQuery)])
                    ->orWhereRaw('LOWER(qr_token) = ?', [mb_strtolower($rawQuery)]);
            })
            ->first();

        if (! $ticket) {
            $scanResult = [
                'status' => 'missing',
                'message' => 'Data tiket tidak ditemukan pada event gate aktif.',
                'gate_id' => $gate->getKey(),
                'gate_name' => $gate->name,
                'gate_code' => $gate->code,
                'query' => $rawQuery,
                'ticket_id' => null,
                'checkin_id' => null,
            ];

            $this->logScanAttempt($scanResult, $gate, $user);

            if ($request->expectsJson()) {
                return response()->json([
                    'scanResult' => $this->resolveScanPayload($scanResult),
                    'gateStats' => $this->buildGateStats($gate),
                    'recentScans' => $this->formatRecentScans($this->buildRecentScans($gate)),
                ]);
            }

            return redirect()
                ->route('gate.dashboard', ['gate' => $gate->getKey()])
                ->withInput(['q' => $rawQuery])
                ->with('gate.scan_result', $scanResult);
        }

        $scanResult = DB::transaction(function () use ($ticket, $gate, $rawQuery, $user): array {
            $lockedTicket = Ticket::query()
                ->with(['student.eventClass', 'event', 'latestCheckin'])
                ->whereKey($ticket->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $existingCheckin = Checkin::query()
                ->where('ticket_id', $lockedTicket->getKey())
                ->latest('checked_in_at')
                ->first();

            if ($existingCheckin) {
                $lockedTicket->setRelation('latestCheckin', $existingCheckin);
                $studentName = $lockedTicket->student?->name;

                return [
                    'status' => 'already_scanned',
                    'message' => filled($studentName)
                        ? "Tiket atas nama {$studentName} sudah pernah masuk."
                        : 'Tiket ini sudah pernah masuk.',
                    'ticket_id' => $lockedTicket->getKey(),
                    'checkin_id' => $existingCheckin->getKey(),
                    'gate_id' => $gate->getKey(),
                    'gate_name' => $gate->name,
                    'gate_code' => $gate->code,
                    'query' => $rawQuery,
                ];
            }

            $checkin = Checkin::query()->create([
                'event_id' => $lockedTicket->event_id,
                'ticket_id' => $lockedTicket->getKey(),
                'event_gate_id' => $gate->getKey(),
                'user_id' => $user?->getKey(),
                'scan_method' => mb_strtolower($rawQuery) === mb_strtolower($lockedTicket->qr_token) ? 'qr' : 'manual',
                'scan_value' => $rawQuery,
                'checked_in_at' => now(),
            ]);

            $lockedTicket->setRelation('latestCheckin', $checkin);

            return [
                'status' => 'success',
                'message' => 'Check-in berhasil.',
                'ticket_id' => $lockedTicket->getKey(),
                'checkin_id' => $checkin->getKey(),
                'gate_id' => $gate->getKey(),
                'gate_name' => $gate->name,
                'gate_code' => $gate->code,
                'query' => $rawQuery,
            ];
        });

        $this->logScanAttempt($scanResult, $gate, $user);

        if ($request->expectsJson()) {
            $scanPayload = $this->resolveScanPayload($scanResult);
            $gateStats = $this->buildGateStats($gate);
            $recentScans = $this->buildRecentScans($gate);

            return response()->json([
                'scanResult' => $scanPayload,
                'gateStats' => $gateStats,
                'recentScans' => $this->formatRecentScans($recentScans),
            ]);
        }

        return redirect()
            ->route('gate.dashboard', ['gate' => $gate->getKey()])
            ->withInput(['q' => $rawQuery])
            ->with('gate.scan_result', $scanResult);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('gate.login');
    }

    public function stats(Request $request): JsonResponse
    {
        $user = $request->user();
        $gates = $this->accessibleGatesQuery($user)->get();

        if ($gates->isEmpty()) {
            return response()->json([
                'gate_id' => null,
                'stats' => $this->buildGateStats(null),
            ]);
        }

        $selectedGateId = $request->integer('gate');
        $selectedGate = $gates->firstWhere('id', $selectedGateId) ?? $gates->first();

        return response()->json([
            'gate_id' => $selectedGate->getKey(),
            'stats' => $this->buildGateStats($selectedGate),
        ]);
    }

    public function recentScans(Request $request): JsonResponse
    {
        $user = $request->user();
        $gates = $this->accessibleGatesQuery($user)->get();

        if ($gates->isEmpty()) {
            return response()->json([
                'gate_id' => null,
                'scans' => [],
            ]);
        }

        $selectedGateId = $request->integer('gate');
        $selectedGate = $gates->firstWhere('id', $selectedGateId) ?? $gates->first();

        $scans = $this->buildRecentScans($selectedGate)
            ->values();

        return response()->json([
            'gate_id' => $selectedGate->getKey(),
            'scans' => $this->formatRecentScans($scans),
        ]);
    }

    public function history(Request $request): View
    {
        $user = $request->user();
        abort_unless($user?->hasRole('super_admin'), 403, 'Anda tidak memiliki akses ke riwayat scan.');

        $gates = $this->accessibleGatesQuery($user)->get();
        $selectedGateId = $request->integer('gate');
        $search = trim((string) $request->query('q', ''));
        $scanMethod = trim((string) $request->query('method', 'all'));

        $historyQuery = ScanAttempt::query()
            ->with(['gate', 'event'])
            ->whereIn('event_gate_id', $gates->pluck('id'));

        if ($selectedGateId > 0 && $gates->contains('id', $selectedGateId)) {
            $historyQuery->where('event_gate_id', $selectedGateId);
        }

        if ($search !== '') {
            $historyQuery->where(function (Builder $query) use ($search): void {
                $query
                    ->where('ticket_code', 'like', "%{$search}%")
                    ->orWhere('student_name', 'like', "%{$search}%")
                    ->orWhere('query', 'like', "%{$search}%");
            });
        }

        if (in_array($scanMethod, ['qr', 'manual', 'success', 'missing', 'already_scanned'], true)) {
            if (in_array($scanMethod, ['qr', 'manual'], true)) {
                $historyQuery->where('scan_method', $scanMethod);
            } else {
                $historyQuery->where('status', $scanMethod);
            }
        } else {
            $scanMethod = 'all';
        }

        $historyScans = (clone $historyQuery)
            ->latest('scanned_at')
            ->paginate(10)
            ->withQueryString();

        $todayStatsBase = ScanAttempt::query()
            ->whereIn('event_gate_id', $gates->pluck('id'))
            ->whereDate('scanned_at', today());

        if ($selectedGateId > 0 && $gates->contains('id', $selectedGateId)) {
            $todayStatsBase->where('event_gate_id', $selectedGateId);
        }

        $todayTotal = (clone $todayStatsBase)->count();
        $todaySuccess = (clone $todayStatsBase)->where('status', 'success')->count();
        $todayAlreadyScanned = (clone $todayStatsBase)->where('status', 'already_scanned')->count();
        $todayMissing = (clone $todayStatsBase)->where('status', 'missing')->count();

        return view('gate.history', [
            'user' => $user,
            'gates' => $gates,
            'selectedGateId' => $selectedGateId,
            'search' => $search,
            'scanMethod' => $scanMethod,
            'historyScans' => $historyScans,
            'todayTotal' => $todayTotal,
            'todaySuccess' => $todaySuccess,
            'todayAlreadyScanned' => $todayAlreadyScanned,
            'todayMissing' => $todayMissing,
        ]);
    }

    private function accessibleGatesQuery($user): Builder
    {
        $query = EventGate::query()->with(['event', 'assignedUsers']);

        if ($user?->hasRole('super_admin')) {
            return $query->orderBy('event_id')->orderBy('name');
        }

        return $query
            ->whereHas('assignedUsers', function ($assignedUsersQuery) use ($user): void {
                $assignedUsersQuery->whereKey($user->getKey());
            })
            ->orderBy('event_id')
            ->orderBy('name');
    }

    private function buildGateStats(?EventGate $selectedGate): array
    {
        if (! $selectedGate) {
            return [
                'total_hadir' => 0,
                'belum_scan' => 0,
                'sudah_scan' => 0,
                'ditolak' => 0,
            ];
        }

        $eventId = $selectedGate->event_id;

        $totalStudents = Student::query()
            ->where('event_id', $eventId)
            ->count();

        $successfulCheckins = Checkin::query()
            ->where('event_id', $eventId)
            ->where('event_gate_id', $selectedGate->getKey())
            ->count();

        $uniqueCheckedTickets = Checkin::query()
            ->where('event_id', $eventId)
            ->where('event_gate_id', $selectedGate->getKey())
            ->distinct('ticket_id')
            ->count('ticket_id');

        $rejectedTickets = Ticket::query()
            ->where('event_id', $eventId)
            ->whereIn('status', ['revoked', 'cancelled'])
            ->count();

        return [
            'total_hadir' => $uniqueCheckedTickets,
            'belum_scan' => max($totalStudents - $uniqueCheckedTickets, 0),
            'sudah_scan' => $successfulCheckins,
            'ditolak' => $rejectedTickets,
        ];
    }

    private function buildRecentScans(?EventGate $selectedGate)
    {
        if (! $selectedGate) {
            return collect();
        }

        return ScanAttempt::query()
            ->where('event_gate_id', $selectedGate->getKey())
            ->latest('scanned_at')
            ->limit(10)
            ->get();
    }

    private function formatRecentScans($scans): array
    {
        return collect($scans)
            ->map(function (ScanAttempt $scan): array {
                return [
                    'time' => $scan->scanned_at?->timezone('Asia/Jakarta')->format('H:i:s \W\I\B') ?? '-',
                    'student' => $scan->student_name ?: 'Peserta tidak dikenal',
                    'ticket_code' => $scan->ticket_code ?: ($scan->query ?: '-'),
                    'status' => $this->mapScanStatusLabel($scan->status, $scan->scan_method),
                ];
            })
            ->values()
            ->all();
    }

    private function mapScanStatusLabel(?string $status, ?string $scanMethod = null): string
    {
        return match ($status) {
            'success' => strtoupper($scanMethod ?? 'qr'),
            'already_scanned' => 'Sudah Digunakan',
            'missing' => 'Invalid',
            default => ucfirst((string) ($scanMethod ?? $status ?? 'scan')),
        };
    }

    private function logScanAttempt(array $scanResult, EventGate $gate, $user): void
    {
        $ticket = filled($scanResult['ticket_id'] ?? null)
            ? Ticket::query()->with(['student.eventClass'])->find($scanResult['ticket_id'])
            : null;

        $isQrMethod = $ticket
            ? mb_strtolower((string) ($scanResult['query'] ?? '')) === mb_strtolower((string) $ticket->qr_token)
            : false;

        ScanAttempt::query()->create([
            'event_id' => $ticket?->event_id ?? $gate->event_id,
            'ticket_id' => $ticket?->getKey(),
            'event_gate_id' => $gate->getKey(),
            'user_id' => $user?->getKey(),
            'query' => $scanResult['query'] ?? null,
            'status' => $scanResult['status'] ?? 'missing',
            'scan_method' => $ticket ? ($isQrMethod ? 'qr' : 'manual') : null,
            'student_name' => $ticket?->student?->name,
            'class_name' => $ticket?->student?->eventClass?->name,
            'ticket_code' => $ticket?->ticket_code,
            'scanned_at' => now(),
        ]);
    }

    private function resolveScanPayload(array $scanResult): array
    {
        $ticket = filled($scanResult['ticket_id'] ?? null)
            ? Ticket::query()->with(['student.eventClass', 'event', 'latestCheckin'])->find($scanResult['ticket_id'])
            : null;

        $checkin = filled($scanResult['checkin_id'] ?? null)
            ? Checkin::query()->find($scanResult['checkin_id'])
            : null;

        return [
            'status' => $scanResult['status'] ?? 'missing',
            'message' => $scanResult['message'] ?? '',
            'gate_name' => $scanResult['gate_name'] ?? null,
            'gate_code' => $scanResult['gate_code'] ?? null,
            'query' => $scanResult['query'] ?? null,
            'ticket' => $ticket ? [
                'name' => $ticket->student?->name ?? '-',
                'class' => $ticket->student?->eventClass?->name ?? '-',
                'mother_name' => $ticket->student?->mother_name ?? '-',
                'mother_whatsapp' => $ticket->student?->mother_whatsapp ?? '-',
                'ticket_code' => $ticket->ticket_code,
                'qr_token' => $ticket->qr_token,
                'event_name' => $ticket->event?->name ?? '-',
            ] : null,
            'checkin' => $checkin ? [
                'checked_in_at' => $checkin->checked_in_at?->timezone('Asia/Jakarta')->format('d/m/Y H:i:s \W\I\B'),
                'scan_method' => $checkin->scan_method,
            ] : null,
        ];
    }
}
