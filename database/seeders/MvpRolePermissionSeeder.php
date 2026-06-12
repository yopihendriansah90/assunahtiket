<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class MvpRolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach ([
            'ViewAny:ScanAttempt',
            'View:ScanAttempt',
            'Create:ScanAttempt',
            'Update:ScanAttempt',
            'Delete:ScanAttempt',
            'DeleteAny:ScanAttempt',
            'Restore:ScanAttempt',
            'RestoreAny:ScanAttempt',
            'ForceDelete:ScanAttempt',
            'ForceDeleteAny:ScanAttempt',
            'Replicate:ScanAttempt',
            'Reorder:ScanAttempt',
        ] as $permissionName) {
            Permission::findOrCreate($permissionName, 'web');
        }

        $this->syncRolePermissions(
            'super_admin',
            Permission::query()->pluck('name')->all(),
        );

        $this->syncRolePermissionsBySubjects('event_admin', [
            'Event',
            'EventClass',
            'Student',
            'EventGate',
            'ScanAttempt',
        ]);

        $this->syncRolePermissionsBySubjects('pic_sekolah', [
            'Event',
            'Student',
            'ScanAttempt',
        ], [
            'Create:Event',
            'Update:Event',
            'Delete:Event',
            'DeleteAny:Event',
            'Create:EventClass',
            'Update:EventClass',
            'Delete:EventClass',
            'DeleteAny:EventClass',
            'Create:EventGate',
            'Update:EventGate',
            'Delete:EventGate',
            'DeleteAny:EventGate',
            'Create:ScanAttempt',
            'Update:ScanAttempt',
            'Delete:ScanAttempt',
            'DeleteAny:ScanAttempt',
            'Restore:ScanAttempt',
            'RestoreAny:ScanAttempt',
            'ForceDelete:ScanAttempt',
            'ForceDeleteAny:ScanAttempt',
            'Replicate:ScanAttempt',
            'Reorder:ScanAttempt',
        ]);

        $this->syncRolePermissionsBySubjects('helper_desk', [
            'Event',
            'Student',
            'ScanAttempt',
        ], [
            'Create:Event',
            'Update:Event',
            'Delete:Event',
            'DeleteAny:Event',
            'Create:Student',
            'Update:Student',
            'Delete:Student',
            'DeleteAny:Student',
            'Create:ScanAttempt',
            'Update:ScanAttempt',
            'Delete:ScanAttempt',
            'DeleteAny:ScanAttempt',
            'Restore:ScanAttempt',
            'RestoreAny:ScanAttempt',
            'ForceDelete:ScanAttempt',
            'ForceDeleteAny:ScanAttempt',
            'Replicate:ScanAttempt',
            'Reorder:ScanAttempt',
        ]);

        $this->syncRolePermissionsBySubjects('checkin_officer', [
            'Event',
            'Student',
            'ScanAttempt',
        ], [
            'Create:Event',
            'Update:Event',
            'Delete:Event',
            'DeleteAny:Event',
            'Create:Student',
            'Update:Student',
            'Delete:Student',
            'DeleteAny:Student',
            'Create:ScanAttempt',
            'Update:ScanAttempt',
            'Delete:ScanAttempt',
            'DeleteAny:ScanAttempt',
            'Restore:ScanAttempt',
            'RestoreAny:ScanAttempt',
            'ForceDelete:ScanAttempt',
            'ForceDeleteAny:ScanAttempt',
            'Replicate:ScanAttempt',
            'Reorder:ScanAttempt',
        ]);

        User::query()
            ->where('email', 'test@gmail.com')
            ->first()
            ?->assignRole('super_admin');

        $users = [
            [
                'name' => 'Super Admin',
                'email' => 'superadmin@gmail.com',
                'role' => 'super_admin',
            ],
            [
                'name' => 'Event Admin',
                'email' => 'eventadmin@gmail.com',
                'role' => 'event_admin',
            ],
            [
                'name' => 'PIC Sekolah',
                'email' => 'picsekolah@gmail.com',
                'role' => 'pic_sekolah',
            ],
            [
                'name' => 'Helper Desk',
                'email' => 'helperdesk@gmail.com',
                'role' => 'helper_desk',
            ],
            [
                'name' => 'Check-in Officer',
                'email' => 'checkinofficer@gmail.com',
                'role' => 'checkin_officer',
            ],
        ];

        foreach ($users as $userData) {
            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => Hash::make('admin'),
                ]
            );

            $user->syncRoles([$userData['role']]);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function syncRolePermissions(string $roleName, array $permissionNames): void
    {
        $role = Role::findOrCreate($roleName, 'web');

        $permissions = Permission::query()
            ->whereIn('name', $permissionNames)
            ->get();

        $role->syncPermissions($permissions);
    }

    private function syncRolePermissionsBySubjects(string $roleName, array $subjects, array $exclude = []): void
    {
        $permissionNames = Permission::query()
            ->pluck('name')
            ->filter(function (string $permissionName) use ($subjects, $exclude): bool {
                foreach ($subjects as $subject) {
                    if (
                        str_starts_with($permissionName, 'View:' . $subject)
                        || str_starts_with($permissionName, 'ViewAny:' . $subject)
                        || str_starts_with($permissionName, 'Create:' . $subject)
                        || str_starts_with($permissionName, 'Update:' . $subject)
                        || str_starts_with($permissionName, 'Delete:' . $subject)
                        || str_starts_with($permissionName, 'DeleteAny:' . $subject)
                        || str_starts_with($permissionName, 'Restore:' . $subject)
                        || str_starts_with($permissionName, 'RestoreAny:' . $subject)
                        || str_starts_with($permissionName, 'ForceDelete:' . $subject)
                        || str_starts_with($permissionName, 'ForceDeleteAny:' . $subject)
                        || str_starts_with($permissionName, 'Replicate:' . $subject)
                        || str_starts_with($permissionName, 'Reorder:' . $subject)
                    ) {
                        return ! in_array($permissionName, $exclude, true);
                    }
                }

                return false;
            })
            ->values()
            ->all();

        $this->syncRolePermissions($roleName, $permissionNames);
    }
}
