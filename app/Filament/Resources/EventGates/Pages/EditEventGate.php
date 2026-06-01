<?php

namespace App\Filament\Resources\EventGates\Pages;

use App\Filament\Resources\EventGates\EventGateResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditEventGate extends EditRecord
{
    protected static string $resource = EventGateResource::class;

    protected function getSavedNotificationMessage(): ?string
    {
        return 'Data gerbang berhasil diperbarui.';
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->label('Hapus'),
        ];
    }
}
