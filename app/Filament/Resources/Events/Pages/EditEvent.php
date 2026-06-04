<?php

namespace App\Filament\Resources\Events\Pages;

use App\Filament\Resources\Events\EventResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\Enums\ContentTabPosition;
use Filament\Support\Enums\Width;

class EditEvent extends EditRecord
{
    protected static string $resource = EventResource::class;

    public function getMaxContentWidth(): Width|string|null
    {
        return Width::Full;
    }

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }

    public function getContentTabPosition(): ?ContentTabPosition
    {
        return ContentTabPosition::Before;
    }

    protected function getAllRelationManagers(): array
    {
        return EventResource::getRelations();
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Perubahan acara berhasil disimpan')
            ->body(sprintf(
                'Data acara "%s" sudah diperbarui. Anda bisa langsung melanjutkan pengelolaan kelas, siswa, dan pengaturan tiket dari halaman ini.',
                $this->record->name,
            ));
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('lockEvent')
                ->label('Kunci Acara')
                ->icon('heroicon-o-lock-closed')
                ->color('warning')
                ->requiresConfirmation()
                ->visible(fn (): bool => blank($this->record->locked_at) && (auth()->user()?->can('LockData') ?? false))
                ->action(function (): void {
                    $this->record->forceFill([
                        'status' => 'locked',
                        'locked_at' => now(),
                        'locked_by' => auth()->id(),
                    ])->save();

                    Notification::make()
                        ->title('Acara berhasil dikunci')
                        ->success()
                        ->send();
                }),
            DeleteAction::make()
                ->label('Hapus')
                ->visible(fn (): bool => auth()->user()?->can('Delete:Event') ?? false),
        ];
    }
}
