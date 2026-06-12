<?php

namespace App\Filament\Resources\AttendanceReports\Pages;

use App\Filament\Resources\AttendanceReports\AttendanceReportResource;
use App\Filament\Widgets\AttendanceReportOverviewWidget;
use App\Models\Event;
use App\Services\Reports\AttendanceReportExcelExportService;
use Filament\Actions\Action;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Str;

class ListAttendanceReports extends ListRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = AttendanceReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('downloadExcel')
                ->label('Download Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->requiresConfirmation()
                ->modalHeading('Download laporan kehadiran')
                ->modalDescription('File Excel akan mengikuti filter laporan yang sedang aktif pada tabel ini.')
                ->modalSubmitActionLabel('Unduh Excel')
                ->visible(fn (): bool => auth()->user()?->can('ViewAny:ScanAttempt') ?? false)
                ->action(function () {
                    $records = $this->getFilteredSortedTableQuery()
                        ?->with([
                            'event',
                            'eventClass',
                            'ticket.latestCheckin.gate',
                        ])
                        ->get() ?? collect();

                    $selectedEventName = $this->getSelectedEventName();
                    $fileBaseName = filled($selectedEventName)
                        ? 'laporan-kehadiran-' . Str::slug((string) $selectedEventName) . '-' . now()->format('Ymd-His')
                        : 'laporan-kehadiran-' . now()->format('Ymd-His');

                    $service = app(AttendanceReportExcelExportService::class);
                    $temporaryPath = $service->exportToTemporaryFile($records, $fileBaseName);

                    return $service->downloadFile($temporaryPath, strtoupper(str_replace('-', '_', $fileBaseName)) . '.xlsx');
                }),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            AttendanceReportOverviewWidget::class,
        ];
    }

    public function getTitle(): string
    {
        return 'Laporan Kehadiran Event';
    }

    private function getSelectedEventName(): ?string
    {
        $eventId = $this->tableFilters['event_id']['value'] ?? null;

        if (blank($eventId)) {
            return null;
        }

        return Event::query()
            ->whereKey($eventId)
            ->value('name');
    }
}
