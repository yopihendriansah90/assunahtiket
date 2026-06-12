<?php

namespace App\Filament\Pages;

use App\Models\Checkin;
use App\Models\Event;
use App\Services\Reports\AttendanceReportMaintenanceService;
use App\Services\Reports\DatabaseBackupService;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use BackedEnum;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use UnitEnum;

class ResetAttendancePage extends Page
{
    use HasPageShield;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-trash';

    protected static ?string $navigationLabel = 'Reset Kehadiran';

    protected static string|UnitEnum|null $navigationGroup = 'Laporan';

    protected static ?int $navigationSort = 3;

    protected string $view = 'filament-panels::pages.page';

    public ?array $data = [];

    public function getTitle(): string
    {
        return 'Reset Kehadiran';
    }

    public function mount(): void
    {
        $this->form->fill();
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema->statePath('data');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Backup dan Reset')
                ->description('Sistem akan menyiapkan backup database terlebih dahulu, lalu menghapus semua data check-in pada event yang dipilih.')
                ->schema([
                    Select::make('event_id')
                        ->label('Event')
                        ->validationAttribute('event')
                        ->validationMessages([
                            'required' => 'Pilih event yang akan di-reset.',
                        ])
                        ->options(fn (): array => Event::query()
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->all())
                        ->searchable()
                        ->preload()
                        ->required(),
                ]),
        ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([EmbeddedSchema::make('form')])
                ->id('form')
                ->livewireSubmitHandler('downloadBackupAndReset')
                ->footer([
                    Actions::make([
                    Action::make('downloadBackupAndReset')
                            ->label('Backup Lalu Reset')
                            ->icon('heroicon-o-arrow-down-tray')
                            ->color('danger')
                            ->requiresConfirmation()
                            ->modalHeading('Backup lalu reset kehadiran')
                            ->modalDescription(function (): string {
                                $eventId = $this->data['event_id'] ?? null;
                                $event = filled($eventId)
                                    ? Event::query()->find($eventId)
                                    : null;

                                if (! $event) {
                                    return 'Pilih event terlebih dahulu. Setelah itu sistem akan menyiapkan backup database dan menghapus semua check-in pada event tersebut.';
                                }

                                $checkinCount = Checkin::query()
                                    ->where('event_id', $event->getKey())
                                    ->count();

                                return $checkinCount > 0
                                    ? 'Anda akan mengunduh backup database, lalu menghapus ' . number_format($checkinCount) . ' data check-in pada event "' . $event->name . '".'
                                    : 'Event "' . $event->name . '" belum memiliki data check-in. Backup tetap akan diunduh sebelum proses reset dijalankan.';
                            })
                            ->submit('downloadBackupAndReset'),
                    ]),
                ]),
        ]);
    }

    public function downloadBackupAndReset(): BinaryFileResponse
    {
        $data = $this->form->getState();
        $event = Event::query()->findOrFail($data['event_id'] ?? null);

        $backup = app(DatabaseBackupService::class)->createTemporaryBackup(
            'backup-reset-kehadiran-' . $event->code,
        );

        $deleted = app(AttendanceReportMaintenanceService::class)->resetAttendanceForEvent($event);

        Notification::make()
            ->success()
            ->title('Backup database berhasil disiapkan. Reset kehadiran selesai untuk event "' . $event->name . '".')
            ->body($deleted > 0 ? 'Total check-in yang dihapus: ' . number_format($deleted) . '.' : 'Event ini tidak memiliki data check-in.')
            ->send();

        return response()
            ->download($backup['path'], $backup['download_name'])
            ->deleteFileAfterSend(true);
    }
}
