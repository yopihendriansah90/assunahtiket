<?php

namespace App\Services\Tickets;

use App\Models\Student;
use App\Models\Ticket;
use App\Models\TicketFile;
use App\Models\User;
use chillerlan\QRCode\Output\QROutputInterface;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class TicketQrImageService
{
    private string $disk = 'public';

    public function ensureTicketForStudent(Student $student, ?User $generatedBy = null): Ticket
    {
        $student->loadMissing(['event.settings']);
        $this->ensureCanGenerate($student, $generatedBy);

        return DB::transaction(function () use ($student, $generatedBy): Ticket {
            $ticket = Ticket::query()
                ->where('student_id', $student->getKey())
                ->lockForUpdate()
                ->first();

            if ($ticket === null) {
                $ticket = new Ticket();
            }

            $ticket->event_id = $student->event_id;
            $ticket->student_id = $student->getKey();
            $ticket->ticket_code ??= $this->makeTicketCode($student);
            $ticket->qr_token ??= $this->makeQrToken();
            $ticket->status ??= 'active';
            $ticket->generated_at ??= now();
            $ticket->generated_by ??= $generatedBy?->getKey();
            $ticket->save();

            return $ticket;
        });
    }

    public function ensureQrImageForTicket(Ticket $ticket, ?User $generatedBy = null): TicketFile
    {
        $ticket->loadMissing(['event', 'student.event.settings']);

        $relativePath = $ticket->qrFilePath();
        $absolutePath = Storage::disk($this->disk)->path($relativePath);
        $directory = dirname($relativePath);

        Storage::disk($this->disk)->makeDirectory($directory);

        $options = new QROptions([
            'outputType' => QROutputInterface::GDIMAGE_JPG,
            'outputBase64' => false,
            'returnResource' => false,
            'scale' => 12,
            'quality' => 92,
            'imageTransparent' => false,
        ]);

        try {
            (new QRCode($options))->render($this->qrPayload($ticket), $absolutePath);
        } catch (\Throwable $throwable) {
            throw new RuntimeException('QR JPG gagal dibuat: ' . $throwable->getMessage(), previous: $throwable);
        }

        $size = Storage::disk($this->disk)->exists($relativePath)
            ? Storage::disk($this->disk)->size($relativePath)
            : null;

        return TicketFile::query()->updateOrCreate(
            [
                'ticket_id' => $ticket->getKey(),
                'type' => 'qr',
            ],
            [
                'disk' => $this->disk,
                'path' => $relativePath,
                'mime_type' => 'image/jpeg',
                'size' => $size,
                'created_by' => $generatedBy?->getKey(),
            ],
        );
    }

    public function hasStoredQrImage(Ticket $ticket): bool
    {
        return Storage::disk($this->disk)->exists($ticket->qrFilePath());
    }

    public function qrPayload(Ticket $ticket): string
    {
        return $ticket->qrPayload();
    }

    public function downloadFilename(Ticket $ticket): string
    {
        return $ticket->qrDownloadFileName();
    }

    protected function ensureCanGenerate(Student $student, ?User $generatedBy): void
    {
        if ($generatedBy?->hasRole('super_admin')) {
            return;
        }

        if (blank($student->event_id)) {
            throw ValidationException::withMessages([
                'event_id' => 'Acara untuk siswa ini belum dipilih.',
            ]);
        }

        if ($student->event?->isLocked() === true) {
            throw ValidationException::withMessages([
                'event_id' => 'QR tiket pada acara yang sudah dikunci tidak dapat dibuat atau diperbarui.',
            ]);
        }
    }

    protected function makeTicketCode(Student $student): string
    {
        $prefix = $student->event?->settings?->ticket_code_prefix
            ?? $student->event?->code
            ?? 'TKT';

        $prefix = Str::of($prefix)
            ->ascii()
            ->upper()
            ->replaceMatches('/[^A-Z0-9]/', '')
            ->toString();

        $prefix = $prefix !== '' ? $prefix : 'TKT';

        return sprintf('%s-%05d', $prefix, $student->getKey());
    }

    protected function makeQrToken(): string
    {
        do {
            $token = bin2hex(random_bytes(13));
        } while (Ticket::query()->where('qr_token', $token)->exists());

        return $token;
    }
}
