<?php

namespace App\Services\Reports;

use App\Models\Checkin;
use App\Models\Event;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AttendanceReportMaintenanceService
{
    public function createCheckin(Student $student, array $data, ?User $operator = null): Checkin
    {
        $ticket = $student->ticket;

        if ($ticket === null) {
            throw ValidationException::withMessages([
                'ticket_id' => 'Peserta ini belum memiliki tiket, sehingga check-in tidak bisa dibuat.',
            ]);
        }

        return Checkin::query()->create([
            'event_id' => $student->event_id,
            'ticket_id' => $ticket->getKey(),
            'event_gate_id' => $data['event_gate_id'] ?? null,
            'user_id' => $data['user_id'] ?? $operator?->getKey(),
            'scan_method' => $data['scan_method'] ?? 'manual',
            'scan_value' => $data['scan_value'] ?? null,
            'checked_in_at' => $data['checked_in_at'] ?? now(),
        ]);
    }

    public function updateLatestCheckin(Student $student, array $data, ?User $operator = null): Checkin
    {
        $checkin = $student->ticket?->latestCheckin;

        if ($checkin === null) {
            return $this->createCheckin($student, $data, $operator);
        }

        $checkin->fill([
            'event_gate_id' => $data['event_gate_id'] ?? $checkin->event_gate_id,
            'user_id' => $data['user_id'] ?? $operator?->getKey() ?? $checkin->user_id,
            'scan_method' => $data['scan_method'] ?? $checkin->scan_method,
            'scan_value' => $data['scan_value'] ?? $checkin->scan_value,
            'checked_in_at' => $data['checked_in_at'] ?? $checkin->checked_in_at,
        ]);

        $checkin->save();

        return $checkin;
    }

    public function resetAttendanceForStudent(Student $student): int
    {
        $ticket = $student->ticket;

        if ($ticket === null) {
            return 0;
        }

        return $ticket->checkins()->delete();
    }

    public function resetAttendanceForEvent(Event $event): int
    {
        return DB::transaction(function () use ($event): int {
            return Checkin::query()
                ->where('event_id', $event->getKey())
                ->delete();
        });
    }

    /**
     * @param iterable<Student> $students
     */
    public function resetAttendanceForStudents(iterable $students): int
    {
        $totalDeleted = 0;

        foreach ($students as $student) {
            if (! $student instanceof Student) {
                continue;
            }

            $totalDeleted += $this->resetAttendanceForStudent($student);
        }

        return $totalDeleted;
    }
}
