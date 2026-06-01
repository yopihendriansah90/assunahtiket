<?php

namespace App\Filament\Resources\EventClasses\Pages;

use App\Filament\Resources\EventClasses\EventClassResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEventClasses extends ListRecords
{
    protected static string $resource = EventClassResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Tambah Kelas'),
        ];
    }
}
