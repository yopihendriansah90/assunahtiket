<?php

namespace Tests\Feature;

use App\Filament\Resources\AttendanceReports\AttendanceReportResource;
use App\Models\Event;
use App\Models\EventClass;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AttendanceReportAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_event_admin_can_see_all_students_in_attendance_report_without_checkin(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Permission::findOrCreate('ViewAny:ScanAttempt', 'web');

        $role = Role::findOrCreate('event_admin', 'web');
        $role->givePermissionTo('ViewAny:ScanAttempt');

        $user = User::factory()->create();
        $user->assignRole('event_admin');

        $event = Event::query()->create([
            'name' => 'Testing Event',
            'code' => 'TEST-ATT-001',
            'status' => 'active',
        ]);

        $class = EventClass::query()->create([
            'event_id' => $event->getKey(),
            'name' => 'A',
        ]);

        Student::query()->create([
            'event_id' => $event->getKey(),
            'class_id' => $class->getKey(),
            'name' => 'Siswa Hadir',
            'gender' => Student::GENDER_MALE,
            'mother_name' => 'Ibu Hadir',
        ]);

        Student::query()->create([
            'event_id' => $event->getKey(),
            'class_id' => $class->getKey(),
            'name' => 'Siswa Belum Hadir',
            'gender' => Student::GENDER_FEMALE,
            'mother_name' => 'Ibu Belum Hadir',
        ]);

        $this->actingAs($user);

        $records = AttendanceReportResource::getEloquentQuery()->get();

        $this->assertCount(2, $records);
        $this->assertTrue($user->can('ViewAny:ScanAttempt'));
        $this->assertFalse($user->can('ViewAny:Checkin'));
    }
}
