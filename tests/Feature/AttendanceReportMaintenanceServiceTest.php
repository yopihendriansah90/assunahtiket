<?php

namespace Tests\Feature;

use App\Models\Checkin;
use App\Models\Event;
use App\Models\EventClass;
use App\Models\EventGate;
use App\Models\Student;
use App\Models\Ticket;
use App\Models\User;
use App\Services\Reports\AttendanceReportMaintenanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Tests\TestCase;

class AttendanceReportMaintenanceServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_update_and_reset_a_student_attendance_record(): void
    {
        $event = Event::query()->create([
            'name' => 'Testing Event',
            'code' => 'TEST-001',
            'status' => 'active',
        ]);

        $class = EventClass::query()->create([
            'event_id' => $event->getKey(),
            'name' => 'A',
        ]);

        $student = Student::query()->create([
            'event_id' => $event->getKey(),
            'class_id' => $class->getKey(),
            'name' => 'Siswa Testing',
            'gender' => Student::GENDER_MALE,
            'mother_name' => 'Ibu Testing',
        ]);

        Ticket::query()->create([
            'event_id' => $event->getKey(),
            'student_id' => $student->getKey(),
            'ticket_code' => 'TCK-001',
            'qr_token' => Str::random(26),
        ]);

        $gate = EventGate::query()->create([
            'event_id' => $event->getKey(),
            'name' => 'Gerbang Utama',
        ]);

        $operator = User::factory()->create();
        $service = app(AttendanceReportMaintenanceService::class);
        $initialCheckedInAt = Carbon::parse('2026-06-12 09:00:00');

        $created = $service->createCheckin($student, [
            'event_gate_id' => $gate->getKey(),
            'user_id' => $operator->getKey(),
            'scan_method' => 'manual',
            'scan_value' => 'TEST-QR-001',
            'checked_in_at' => $initialCheckedInAt,
        ], $operator);

        $this->assertInstanceOf(Checkin::class, $created);
        $this->assertSame($gate->getKey(), $created->event_gate_id);
        $this->assertSame('manual', $created->scan_method);

        $this->assertDatabaseHas('checkins', [
            'id' => $created->getKey(),
            'ticket_id' => $student->ticket->getKey(),
            'scan_value' => 'TEST-QR-001',
        ]);

        $updatedCheckedInAt = Carbon::parse('2026-06-12 09:30:00');
        $updated = $service->updateLatestCheckin($student, [
            'event_gate_id' => $gate->getKey(),
            'user_id' => $operator->getKey(),
            'scan_method' => 'qr',
            'scan_value' => 'TEST-QR-002',
            'checked_in_at' => $updatedCheckedInAt,
        ], $operator);

        $this->assertSame($created->getKey(), $updated->getKey());
        $this->assertSame('qr', $updated->scan_method);
        $this->assertSame('TEST-QR-002', $updated->scan_value);

        $this->assertDatabaseHas('checkins', [
            'id' => $created->getKey(),
            'scan_method' => 'qr',
            'scan_value' => 'TEST-QR-002',
        ]);

        $deleted = $service->resetAttendanceForStudent($student);

        $this->assertSame(1, $deleted);
        $this->assertDatabaseMissing('checkins', [
            'id' => $created->getKey(),
        ]);
    }

    public function test_can_reset_attendance_for_multiple_students(): void
    {
        $event = Event::query()->create([
            'name' => 'Testing Event',
            'code' => 'TEST-002',
            'status' => 'active',
        ]);

        $class = EventClass::query()->create([
            'event_id' => $event->getKey(),
            'name' => 'A',
        ]);

        $gate = EventGate::query()->create([
            'event_id' => $event->getKey(),
            'name' => 'Gerbang Utama',
        ]);

        $operator = User::factory()->create();
        $service = app(AttendanceReportMaintenanceService::class);

        $students = collect([
            Student::query()->create([
                'event_id' => $event->getKey(),
                'class_id' => $class->getKey(),
                'name' => 'Siswa Satu',
                'gender' => Student::GENDER_MALE,
                'mother_name' => 'Ibu Satu',
            ]),
            Student::query()->create([
                'event_id' => $event->getKey(),
                'class_id' => $class->getKey(),
                'name' => 'Siswa Dua',
                'gender' => Student::GENDER_FEMALE,
                'mother_name' => 'Ibu Dua',
            ]),
        ]);

        $students->each(function (Student $student) use ($event, $gate, $operator, $service): void {
            Ticket::query()->create([
                'event_id' => $event->getKey(),
                'student_id' => $student->getKey(),
                'ticket_code' => 'TCK-' . str_pad((string) $student->getKey(), 3, '0', STR_PAD_LEFT),
                'qr_token' => Str::random(26),
            ]);

            $service->createCheckin($student, [
                'event_gate_id' => $gate->getKey(),
                'user_id' => $operator->getKey(),
                'scan_method' => 'manual',
                'checked_in_at' => Carbon::parse('2026-06-12 10:00:00'),
            ], $operator);
        });

        $this->assertSame(2, Checkin::query()->count());

        $deleted = $service->resetAttendanceForStudents($students);

        $this->assertSame(2, $deleted);
        $this->assertSame(0, Checkin::query()->count());
    }
}
