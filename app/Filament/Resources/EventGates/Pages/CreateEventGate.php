<?php

namespace App\Filament\Resources\EventGates\Pages;

use App\Filament\Resources\EventGates\EventGateResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEventGate extends CreateRecord
{
    protected static string $resource = EventGateResource::class;

    protected function getCreatedNotificationMessage(): ?string
    {
        return 'Data gerbang berhasil disimpan.';
    }
}
