<?php

namespace App\Filament\Resources\Events\Pages;

use App\Filament\Resources\Events\EventResource;
use Filament\Actions\CreateAction;
use Filament\Facades\Filament;
use Filament\Resources\Pages\ListRecords;

class ListEvents extends ListRecords
{
    protected static string $resource = EventResource::class;

    protected function getHeaderActions(): array
    {
        if (Filament::getCurrentPanel()?->getId() === 'picsekolah') {
            return [];
        }

        return [
            CreateAction::make()->label('Tambah Acara'),
        ];
    }
}
