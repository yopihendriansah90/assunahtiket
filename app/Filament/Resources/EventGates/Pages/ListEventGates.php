<?php

namespace App\Filament\Resources\EventGates\Pages;

use App\Filament\Resources\EventGates\EventGateResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEventGates extends ListRecords
{
    protected static string $resource = EventGateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Tambah Gerbang'),
        ];
    }
}
