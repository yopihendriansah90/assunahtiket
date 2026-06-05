<?php

namespace App\Filament\Resources\Checkins\Pages;

use App\Filament\Resources\Checkins\CheckinResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Width;

class EditCheckin extends EditRecord
{
    protected static string $resource = CheckinResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    public function getMaxContentWidth(): Width|string|null
    {
        return Width::Full;
    }
}
