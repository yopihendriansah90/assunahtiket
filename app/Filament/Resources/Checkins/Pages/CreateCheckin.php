<?php

namespace App\Filament\Resources\Checkins\Pages;

use App\Filament\Resources\Checkins\CheckinResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Width;

class CreateCheckin extends CreateRecord
{
    protected static string $resource = CheckinResource::class;

    public function getMaxContentWidth(): Width|string|null
    {
        return Width::Full;
    }
}
