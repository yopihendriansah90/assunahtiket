<?php

namespace App\Filament\Resources\EventClasses\Pages;

use App\Filament\Resources\EventClasses\EventClassResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEventClass extends CreateRecord
{
    protected static string $resource = EventClassResource::class;

    protected function getCreatedNotificationMessage(): ?string
    {
        return 'Data kelas berhasil disimpan.';
    }
}
