<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\AttendanceReports\Pages\ListAttendanceReports;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AttendanceReportOverviewWidget extends StatsOverviewWidget
{
    use InteractsWithPageTable;

    protected ?string $heading = 'Ringkasan Kehadiran';

    protected ?string $description = 'Ringkasan ini mengikuti filter event, kelas, pencarian, dan status kehadiran pada tabel laporan.';

    protected function getTablePage(): string
    {
        return ListAttendanceReports::class;
    }

    protected function getStats(): array
    {
        $baseQuery = $this->getPageTableQuery();

        $totalParticipants = (clone $baseQuery)->count();
        $presentParticipants = (clone $baseQuery)->whereHas('ticket.checkins')->count();
        $absentParticipants = (clone $baseQuery)->whereDoesntHave('ticket.checkins')->count();
        $attendanceRate = $totalParticipants > 0
            ? number_format(($presentParticipants / $totalParticipants) * 100, 1) . '%'
            : '0.0%';

        return [
            Stat::make('Total Peserta', number_format($totalParticipants))
                ->description('Jumlah peserta sesuai filter laporan aktif')
                ->color('primary'),
            Stat::make('Hadir', number_format($presentParticipants))
                ->description('Peserta yang sudah memiliki catatan check-in')
                ->color('success'),
            Stat::make('Belum Hadir', number_format($absentParticipants))
                ->description('Peserta yang belum memiliki catatan check-in')
                ->color('danger'),
            Stat::make('Persentase Kehadiran', $attendanceRate)
                ->description('Perbandingan hadir terhadap total peserta')
                ->color('info'),
        ];
    }
}
