<?php

namespace App\Filament\Resources\Students\Pages;

use App\Filament\Resources\Students\StudentResource;
use App\Models\Student;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

class EditStudent extends EditRecord
{
    protected static string $resource = StudentResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $eventId = $data['event_id'] ?? $this->getRecord()->event_id;

        if ((auth()->user()?->hasRole('super_admin') ?? false) === false && Student::isEventLocked($eventId)) {
            throw ValidationException::withMessages([
                'event_id' => 'Data siswa pada acara yang sudah dikunci tidak dapat diubah.',
            ]);
        }

        return $data;
    }

    protected function getSavedNotificationMessage(): ?string
    {
        return 'Data siswa berhasil diperbarui.';
    }

    protected function getHeaderActions(): array
    {
        $canBypassLock = auth()->user()?->hasRole('super_admin') ?? false;

        return [
            DeleteAction::make()
                ->label('Hapus')
                ->visible(fn (Student $record): bool => ! $record->event?->isLocked() || $canBypassLock),
        ];
    }
}
