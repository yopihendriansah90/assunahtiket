<?php

namespace App\Filament\Resources\EventClasses;

use App\Filament\Resources\EventClasses\Pages\CreateEventClass;
use App\Filament\Resources\EventClasses\Pages\EditEventClass;
use App\Filament\Resources\EventClasses\Pages\ListEventClasses;
use App\Filament\Resources\EventClasses\RelationManagers\AssignedUsersRelationManager;
use App\Filament\Resources\EventClasses\Schemas\EventClassForm;
use App\Filament\Resources\EventClasses\Tables\EventClassesTable;
use App\Models\EventClass;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class EventClassResource extends Resource
{
    protected static ?string $model = EventClass::class;
    protected static ?string $recordTitleAttribute = 'name';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;
    protected static ?string $navigationLabel = 'Kelas';
    protected static string|UnitEnum|null $navigationGroup = 'Data Master';
    protected static ?int $navigationSort = 2;
    protected static ?string $modelLabel = 'Kelas';
    protected static ?string $pluralModelLabel = 'Kelas';

    public static function form(Schema $schema): Schema
    {
        return EventClassForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EventClassesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            AssignedUsersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEventClasses::route('/'),
            'create' => CreateEventClass::route('/create'),
            'edit' => EditEventClass::route('/{record}/edit'),
        ];
    }
}
