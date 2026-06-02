<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Services\Tickets\TicketQrImageService;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class StudentTicketQrDownloadController extends Controller
{
    public function __invoke(Student $student, TicketQrImageService $service): BinaryFileResponse
    {
        $user = auth()->user();

        abort_unless($user !== null, 403);
        abort_unless($user->can('ViewAny:Student') || $user->hasRole('super_admin'), 403);

        $ticket = $student->ticket()->first();

        if ($ticket !== null && $service->hasStoredQrImage($ticket)) {
            return response()->download(
                Storage::disk('public')->path($ticket->qrFilePath()),
                $service->downloadFilename($ticket),
                ['Content-Type' => 'image/jpeg'],
            );
        }

        $ticket = $service->ensureTicketForStudent($student, $user);
        $file = $service->ensureQrImageForTicket($ticket, $user);

        return response()->download(
            Storage::disk($file->disk)->path($file->path),
            $service->downloadFilename($ticket),
            ['Content-Type' => 'image/jpeg'],
        );
    }
}
