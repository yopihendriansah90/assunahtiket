<?php

namespace App\Filament\Resources\EventClasses\Pages;

use App\Filament\Resources\EventClasses\EventClassResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditEventClass extends EditRecord
{
    protected static string $resource = EventClassResource::class;

    protected function getSavedNotificationMessage(): ?string
    {
        return 'Data kelas berhasil diperbarui.';
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->label('Hapus'),
        ];
    }
}
