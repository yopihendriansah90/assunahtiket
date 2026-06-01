<?php

namespace App\Filament\Resources\Events\Pages;

use App\Filament\Resources\Events\EventResource;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Resources\Pages\Enums\ContentTabPosition;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\Width;
use Illuminate\Contracts\Support\Htmlable;

class ViewEvent extends ViewRecord
{
    protected static string $resource = EventResource::class;

    public function getMaxContentWidth(): Width|string|null
    {
        return Width::Full;
    }

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }

    public function getContentTabPosition(): ?ContentTabPosition
    {
        return ContentTabPosition::Before;
    }

    public function getContentTabLabel(): ?string
    {
        return 'Info Acara';
    }

    public function getContentTabIcon(): string|BackedEnum|Htmlable|null
    {
        return 'heroicon-o-information-circle';
    }

    protected function getAllRelationManagers(): array
    {
        return EventResource::getRelations();
    }

    protected function getHeaderActions(): array
    {
        if (Filament::getCurrentPanel()?->getId() === 'picsekolah') {
            return [];
        }

        return [
            EditAction::make()
                ->label('Ubah')
                ->visible(fn (): bool => auth()->user()?->can('Update:Event') ?? false),
            DeleteAction::make()
                ->label('Hapus')
                ->visible(fn (): bool => auth()->user()?->can('Delete:Event') ?? false),
        ];
    }
}
