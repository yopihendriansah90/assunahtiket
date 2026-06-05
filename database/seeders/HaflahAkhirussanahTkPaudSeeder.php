<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\EventClass;
use App\Models\EventSetting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class HaflahAkhirussanahTkPaudSeeder extends Seeder
{
    public function run(): void
    {
        Role::findOrCreate('pic_sekolah', 'web');
        $accountRows = [];

        $event = Event::updateOrCreate(
            ['name' => 'Haflah Akhirussanah TK/PAUD Ihya As-Sunnah'],
            [
                'code' => 'HAFTK-001',
                'status' => 'draft',
                'event_date' => null,
                'location' => null,
            ],
        );

        EventSetting::updateOrCreate(
            ['event_id' => $event->getKey()],
            [
                'ticket_code_prefix' => null,
                'ticket_sequence_start' => 1,
                'settings' => null,
            ],
        );

        $classNames = [
            'TAAM 1 Al-Hadiid',
            'TAAM 2 Al-Maa\'uun',
            'TAAM 3 Al-Qolam',
            'TAAM 4 Al-Qomar',
            'TAAM 5 Al-Insaan',
            'TAAM 6 An-Nahl',
            'TK A1 An-Najm',
            'TK A2 An-Naba’',
            'TK A3 Al-Fath',
            'TK A4 Al-A\'laa',
            'TK A5 Al-Mu\'minuun',
            'TK A6 Ash Shof',
            'TK A7 Az-Zumar',
            'TK A8 Al-Mursalaat',
            'TK B1 Al-Kahfi',
            'TK B2 Al-Mulk',
            'TK B3 As-Sajdah',
            'TK B4 Al-Qodr',
            'TK B5 At-Taubah',
            'TK B6 Al-Hajj',
            'TK B7 An-Naml',
            'TK B8 Al-\'Ankabut',
            'TK B9 Az-Zukhruf',
            'TK B10 Al-Ma\'aarij',
        ];

        $classesByName = collect($classNames)
            ->values()
            ->mapWithKeys(function (string $className, int $index) use ($event): array {
                $class = EventClass::updateOrCreate(
                    [
                        'event_id' => $event->getKey(),
                        'name' => $className,
                    ],
                    [
                        'sort_order' => $index + 1,
                    ],
                );

                return [$className => $class];
            });

        $teachers = [
            [
                'name' => 'Herma',
                'email' => 'khoerunnisaherma@gmail.com',
                'classes' => ['TAAM 1 Al-Hadiid', 'TAAM 2 Al-Maa\'uun'],
            ],
            [
                'name' => 'Ade',
                'email' => 'aderohmatia22@gmail.com',
                'classes' => ['TAAM 3 Al-Qolam', 'TAAM 4 Al-Qomar'],
            ],
            [
                'name' => 'Hikmah',
                'email' => 'hikmahnazilah27@gmail.com',
                'classes' => ['TAAM 5 Al-Insaan', 'TAAM 6 An-Nahl'],
            ],
            [
                'name' => 'Dina',
                'email' => 'dinanuraz28@gmail.com',
                'classes' => ['TK A1 An-Najm'],
            ],
            [
                'name' => 'Naila',
                'email' => 'nailasyahidah2410@gmail.com',
                'classes' => ['TK A2 An-Naba’'],
            ],
            [
                'name' => 'Rika',
                'email' => 'rnovianti14@gmail.com',
                'classes' => ['TK A3 Al-Fath'],
            ],
            [
                'name' => 'Hilma',
                'email' => 'hilmasadiyyah@gmail.com',
                'classes' => ['TK A4 Al-A\'laa'],
            ],
            [
                'name' => 'Fitri',
                'email' => 'fitrisaja\'ah6@gmail.com',
                'classes' => ['TK A5 Al-Mu\'minuun'],
            ],
            [
                'name' => 'Frida',
                'email' => 'fridalistyani2020@gmail.com',
                'classes' => ['TK A6 Ash Shof'],
            ],
            [
                'name' => 'Lina',
                'email' => 'linawidiawati0879@gmail.com',
                'classes' => ['TK A7 Az-Zumar'],
            ],
            [
                'name' => 'Mona',
                'email' => 'traveltasik@gmail.com',
                'classes' => ['TK A8 Al-Mursalaat'],
            ],
            [
                'name' => 'Euis',
                'email' => 'euisfitri53@gmail.com',
                'classes' => ['TK B1 Al-Kahfi'],
            ],
            [
                'name' => 'Alif',
                'email' => 'sitialifiya.hn01@gmail.com',
                'classes' => ['TK B2 Al-Mulk'],
            ],
            [
                'name' => 'Anisa',
                'email' => 'anisa.heristina@gmail.com',
                'classes' => ['TK B3 As-Sajdah'],
            ],
            [
                'name' => 'Titi',
                'email' => 'titinur13@gmail.com',
                'classes' => ['TK B4 Al-Qodr'],
            ],
            [
                'name' => 'Sry Dina',
                'email' => 'srydina7@gmail.com',
                'classes' => ['TK B5 At-Taubah'],
            ],
            [
                'name' => 'Iis',
                'email' => 'iis.isromanah@gmail.com',
                'classes' => ['TK B6 Al-Hajj'],
            ],
            [
                'name' => 'Dela',
                'email' => 'dela06.d0.d0@gmail.com',
                'classes' => ['TK B7 An-Naml'],
            ],
            [
                'name' => 'Erin',
                'email' => 'erinrosananurhadiati@gmail.com',
                'classes' => ['TK B8 Al-\'Ankabut'],
            ],
            [
                'name' => 'Emma',
                'email' => 'zhafiraummi@gmail.com',
                'classes' => ['TK B9 Az-Zukhruf'],
            ],
            [
                'name' => 'Lia',
                'email' => 'sonalia46@gmail.com',
                'classes' => ['TK B10 Al-Ma\'aarij'],
            ],
        ];

        foreach ($teachers as $teacherData) {
            $user = User::query()->firstOrNew([
                'email' => $teacherData['email'],
            ]);

            $wasRecentlyCreated = ! $user->exists;
            $plainPassword = $wasRecentlyCreated ? $this->generateLowercasePassword() : null;

            $user->name = $teacherData['name'];

            if ($wasRecentlyCreated) {
                $user->password = Hash::make($plainPassword);
            }

            $user->save();

            $user->syncRoles(['pic_sekolah']);

            $classIds = collect($teacherData['classes'])
                ->map(fn (string $className): ?int => $classesByName->get($className)?->getKey())
                ->filter()
                ->values()
                ->all();

            $currentEventClassIds = $classesByName
                ->map(fn (EventClass $eventClass): int => $eventClass->getKey())
                ->values()
                ->all();

            $keptAssignmentIds = $user->assignedClasses()
                ->whereNotIn('classes.id', $currentEventClassIds)
                ->pluck('classes.id')
                ->all();

            $user->assignedClasses()->sync([
                ...$keptAssignmentIds,
                ...$classIds,
            ]);

            $accountRows[] = [
                'Nama Wali Kelas' => $teacherData['name'],
                'Nama Kelas' => implode(' & ', $teacherData['classes']),
                'Email Wali Kelas' => $teacherData['email'],
                'Status Akun' => $wasRecentlyCreated ? 'Akun baru' : 'Akun sudah ada',
                'Password' => $plainPassword ?? '(tidak diubah)',
            ];
        }

        $csvPath = $this->writeAccountsCsv($accountRows);

        if ($this->command) {
            $this->command->info('CSV akun PIC Sekolah dibuat di: ' . $csvPath);
        }
    }

    /**
     * @return non-empty-string
     */
    private function generateLowercasePassword(int $length = 8): string
    {
        $alphabet = range('a', 'z');
        $password = '';

        for ($index = 0; $index < $length; $index++) {
            $password .= $alphabet[random_int(0, count($alphabet) - 1)];
        }

        return $password;
    }

    /**
     * @param  array<int, array<string, string>>  $rows
     */
    private function writeAccountsCsv(array $rows): string
    {
        $directory = storage_path('app/seed-exports');

        File::ensureDirectoryExists($directory);

        $path = $directory . '/haflah-akhirussanah-tk-paud-pic-accounts.csv';
        $handle = fopen($path, 'wb');

        if ($handle === false) {
            throw new \RuntimeException('Gagal membuat file CSV akun PIC Sekolah.');
        }

        fputcsv($handle, [
            'Nama Wali Kelas',
            'Nama Kelas',
            'Email Wali Kelas',
            'Status Akun',
            'Password',
        ]);

        foreach ($rows as $row) {
            fputcsv($handle, [
                $row['Nama Wali Kelas'],
                $row['Nama Kelas'],
                $row['Email Wali Kelas'],
                $row['Status Akun'],
                $row['Password'],
            ]);
        }

        fclose($handle);

        return $path;
    }
}
