<?php

use App\Models\Student;
use App\Services\Tickets\TicketQrImageService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('tickets:regenerate-qr {--event_id=} {--student_id=}', function (TicketQrImageService $service) {
    $query = Student::query()
        ->with(['ticket', 'event.settings']);

    $eventId = $this->option('event_id');
    $studentId = $this->option('student_id');

    if (filled($eventId)) {
        $query->where('event_id', $eventId);
    }

    if (filled($studentId)) {
        $query->whereKey($studentId);
    }

    $students = $query->orderBy('id')->get();

    if ($students->isEmpty()) {
        $this->warn('Tidak ada siswa yang cocok untuk diregenerate.');

        return self::SUCCESS;
    }

    $this->info('Regenerating ' . $students->count() . ' QR ticket(s)...');

    $bar = $this->output->createProgressBar($students->count());
    $bar->start();

    foreach ($students as $student) {
        $ticket = $student->ticket ?? $service->ensureTicketForStudent($student);
        $service->ensureQrImageForTicket($ticket);
        $bar->advance();
    }

    $bar->finish();
    $this->newLine(2);
    $this->info('Regenerasi QR selesai.');

    return self::SUCCESS;
})->purpose('Regenerate QR ticket images with the latest label layout and font');
