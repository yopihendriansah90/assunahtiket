<?php

namespace App\Filament\Widgets;

use App\Models\Event;
use App\Models\Student;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;

class PicSekolahOverviewWidget extends StatsOverviewWidget
{
    protected ?string $heading = 'Ringkasan PIC Sekolah';

    protected ?string $description = 'Gambaran singkat acara, kelas, siswa, dan status lock yang menjadi tanggung jawab Anda.';

    protected function getStats(): array
    {
        $user = auth()->user();

        if ($user === null) {
            return [];
        }

        $assignedClassIds = $user->assignedClasses()->pluck('classes.id');

        $eventsQuery = Event::query()
            ->where(function (Builder $query) use ($user): void {
                $query
                    ->whereHas('assignedUsers', fn (Builder $assignedUsersQuery): Builder => $assignedUsersQuery->whereKey($user->getKey()))
                    ->orWhereHas('classes.assignedUsers', fn (Builder $assignedClassUsersQuery): Builder => $assignedClassUsersQuery->whereKey($user->getKey()));
            });

        $eventsCount = (clone $eventsQuery)->count();
        $lockedEventsCount = (clone $eventsQuery)->whereNotNull('locked_at')->count();
        $classesCount = $assignedClassIds->count();
        $studentsCount = Student::query()
            ->whereIn('class_id', $assignedClassIds->all())
            ->count();

        return [
            Stat::make('Acara yang Dipegang', number_format($eventsCount))
                ->description('Acara yang terhubung ke akun PIC Anda')
                ->color('primary'),
            Stat::make('Kelas yang Dipegang', number_format($classesCount))
                ->description('Kelas yang saat ini bisa Anda kelola')
                ->color('info'),
            Stat::make('Total Siswa', number_format($studentsCount))
                ->description('Siswa dari kelas yang menjadi tanggung jawab Anda')
                ->color('success'),
            Stat::make('Status Lock Event', $lockedEventsCount > 0 ? "{$lockedEventsCount} terkunci" : 'Semua terbuka')
                ->description(($eventsCount - $lockedEventsCount) . ' acara masih bisa diubah')
                ->color($lockedEventsCount > 0 ? 'warning' : 'success'),
        ];
    }
}
