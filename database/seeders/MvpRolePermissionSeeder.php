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

        $this->syncRolePermissions(
            'super_admin',
            Permission::query()->pluck('name')->all(),
        );

        $this->syncRolePermissions('event_admin', [
            'ViewAny:Event',
            'View:Event',
            'Create:Event',
            'Update:Event',
            'Delete:Event',
            'DeleteAny:Event',
            'ViewAny:EventClass',
            'View:EventClass',
            'Create:EventClass',
            'Update:EventClass',
            'Delete:EventClass',
            'DeleteAny:EventClass',
        ]);

        $this->syncRolePermissions('pic_sekolah', [
            'ViewAny:Event',
            'View:Event',
            'ViewAny:Student',
            'View:Student',
            'Create:Student',
            'Update:Student',
            'Delete:Student',
            'DeleteAny:Student',
        ]);

        $this->syncRolePermissions('helper_desk', [
            'ViewAny:Event',
            'View:Event',
            'ViewAny:Student',
            'View:Student',
        ]);

        $this->syncRolePermissions('checkin_officer', [
            'ViewAny:Event',
            'View:Event',
            'ViewAny:Student',
            'View:Student',
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
}
