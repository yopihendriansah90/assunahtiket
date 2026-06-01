<?php

namespace App\Filament\Resources\Events\Pages;

use App\Filament\Resources\Events\EventResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Width;

class CreateEvent extends CreateRecord
{
    protected static string $resource = EventResource::class;

    public function getMaxContentWidth(): Width|string|null
    {
        return Width::Full;
    }

    protected function getCreatedNotificationMessage(): ?string
    {
        return 'Data acara berhasil disimpan.';
    }
}
