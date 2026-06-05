<?php

namespace App\Services\Tickets;

use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use ZipArchive;

class TicketQrZipExportService
{
    public function __construct(
        private readonly TicketQrImageService $ticketQrImageService,
    ) {
    }

    public function exportStudents(iterable $students, ?User $requestedBy = null, ?string $archiveBaseName = null): string
    {
        return $this->buildZip(
            students: $students,
            archiveBaseName: $this->downloadBaseName($archiveBaseName ?? 'qr-tiket-bulk'),
            requestedBy: $requestedBy,
        );
    }

    protected function buildZip(iterable $students, string $archiveBaseName, ?User $requestedBy = null): string
    {
        $directory = storage_path('app/tmp/qr-exports');
        if (! is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        $zipPath = $directory . DIRECTORY_SEPARATOR . $archiveBaseName . '-' . Str::random(8) . '.zip';

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Gagal membuka arsip ZIP untuk export QR.');
        }

        foreach ($students as $student) {
            if (! $student instanceof Student) {
                continue;
            }

            $student->loadMissing(['eventClass', 'ticket', 'event.settings']);
            $ticket = $student->ticket;

            if ($ticket === null) {
                $ticket = $this->ticketQrImageService->ensureTicketForStudent($student, $requestedBy);
            }

            $this->ticketQrImageService->ensureQrImageForTicket($ticket, $requestedBy);

            $absolutePath = Storage::disk('public')->path($ticket->qrFilePath());

            if (! is_file($absolutePath)) {
                throw new RuntimeException('File QR tidak ditemukan untuk siswa: ' . $student->name);
            }

            $zip->addFile($absolutePath, $this->makeEntryName($ticket));
        }

        $zip->close();

        return $zipPath;
    }

    protected function makeEntryName(\App\Models\Ticket $ticket): string
    {
        return $ticket->qrFileName();
    }

    protected function downloadBaseName(string $value): string
    {
        $baseName = Str::of($value)
            ->ascii()
            ->replaceMatches('/[^A-Za-z0-9]+/', '_')
            ->trim('_')
            ->upper()
            ->toString();

        return $baseName !== '' ? $baseName : 'QR_TIKET';
    }
}
