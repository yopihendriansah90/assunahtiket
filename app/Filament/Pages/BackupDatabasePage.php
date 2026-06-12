<?php

namespace App\Filament\Pages;

use App\Services\Reports\DatabaseBackupService;
use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use UnitEnum;

class BackupDatabasePage extends Page
{
    use HasPageShield;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-circle-stack';

    protected static ?string $navigationLabel = 'Backup Database';

    protected static string|UnitEnum|null $navigationGroup = 'Maintenance';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament-panels::pages.page';

    public function getTitle(): string
    {
        return 'Backup Database';
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([])
                ->id('form')
                ->livewireSubmitHandler('downloadBackup')
                ->footer([
                    Actions::make([
                        Action::make('downloadBackup')
                            ->label('Unduh Backup Database')
                            ->icon('heroicon-o-arrow-down-tray')
                            ->color('primary')
                            ->requiresConfirmation()
                            ->modalHeading('Unduh backup database')
                            ->modalDescription('Sistem akan membuat salinan database aktif dan mengunduh file backup ke perangkat Anda. Tidak ada data yang akan dihapus.')
                            ->submit('downloadBackup'),
                    ]),
                ]),
            Section::make('Informasi')
                ->description('Halaman ini hanya untuk backup. Tidak ada proses reset atau penghapusan data.')
                ->compact(),
        ]);
    }

    public function downloadBackup(): BinaryFileResponse
    {
        $backup = app(DatabaseBackupService::class)->createTemporaryBackup('backup-database');

        Notification::make()
            ->success()
            ->title('Backup database sedang diunduh.')
            ->body('File backup dibuat dari database aktif saat ini.')
            ->send();

        return response()
            ->download($backup['path'], $backup['download_name'])
            ->deleteFileAfterSend(true);
    }
}
