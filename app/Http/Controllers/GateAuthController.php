<?php

namespace App\Http\Controllers;

use App\Models\Checkin;
use App\Models\EventGate;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
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

        return view('gate.dashboard', [
            'user' => $user,
            'gates' => $gates,
            'selectedGate' => $selectedGate,
            'scanResult' => $scanResult,
        ]);
    }

    public function scan(Request $request): RedirectResponse
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
            return redirect()
                ->route('gate.dashboard', ['gate' => $gate->getKey()])
                ->withInput(['q' => $rawQuery])
                ->with('gate.scan_result', [
                    'status' => 'missing',
                    'message' => 'Data tiket tidak ditemukan pada event gate aktif.',
                    'gate_id' => $gate->getKey(),
                    'gate_name' => $gate->name,
                    'gate_code' => $gate->code,
                    'query' => $rawQuery,
                    'ticket_id' => null,
                    'checkin_id' => null,
                ]);
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

                return [
                    'status' => 'already_scanned',
                    'message' => 'Tiket ini sudah pernah check-in.',
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
}
