<?php

namespace App\Filament\Resources\EventGates;

use App\Filament\Resources\EventGates\Pages\CreateEventGate;
use App\Filament\Resources\EventGates\Pages\EditEventGate;
use App\Filament\Resources\EventGates\Pages\ListEventGates;
use App\Filament\Resources\EventGates\Schemas\EventGateForm;
use App\Filament\Resources\EventGates\Tables\EventGatesTable;
use App\Models\EventGate;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class EventGateResource extends Resource
{
    protected static ?string $model = EventGate::class;
    protected static ?string $recordTitleAttribute = 'name';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;
    protected static ?string $navigationLabel = 'Pintu Masuk';
    protected static string|UnitEnum|null $navigationGroup = 'Operasional';
    protected static ?int $navigationSort = 1;
    protected static ?string $modelLabel = 'Pintu Masuk';
    protected static ?string $pluralModelLabel = 'Pintu Masuk';

    public static function form(Schema $schema): Schema
    {
        return EventGateForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EventGatesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEventGates::route('/'),
            'create' => CreateEventGate::route('/create'),
            'edit' => EditEventGate::route('/{record}/edit'),
        ];
    }
}
