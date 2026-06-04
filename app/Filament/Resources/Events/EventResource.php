<?php

namespace App\Filament\Resources\Events;

use App\Filament\Resources\Events\Pages\CreateEvent;
use App\Filament\Resources\Events\Pages\EditEvent;
use App\Filament\Resources\Events\Pages\ListEvents;
use App\Filament\Resources\Events\Pages\ViewEvent;
use App\Filament\Resources\Events\RelationManagers\ClassesRelationManager;
use App\Filament\Resources\Events\RelationManagers\StudentsRelationManager;
use App\Filament\Resources\Events\Schemas\EventForm;
use App\Filament\Resources\Events\Tables\EventsTable;
use App\Models\Event;
use BackedEnum;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class EventResource extends Resource
{
    protected static ?string $model = Event::class;
    protected static ?string $recordTitleAttribute = 'name';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;
    protected static ?string $navigationLabel = 'Acara';
    protected static string|UnitEnum|null $navigationGroup = 'Data Master';
    protected static ?int $navigationSort = 1;
    protected static ?string $modelLabel = 'Acara';
    protected static ?string $pluralModelLabel = 'Acara';

    public static function form(Schema $schema): Schema
    {
        return EventForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EventsTable::configure($table);
    }

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return Filament::getCurrentPanel()?->getId() === 'picsekolah'
            ? 'Sekolah'
            : 'Data Master';
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();
        $panelId = Filament::getCurrentPanel()?->getId();

        if (! $user || $user->can('ViewAny:Event') === false) {
            return $query->whereRaw('1 = 0');
        }

        if ($panelId === 'picsekolah' && ! $user->hasRole('super_admin')) {
            return $query->whereHas('classes.assignedUsers', function (Builder $assignedClassUsersQuery) use ($user): void {
                $assignedClassUsersQuery->whereKey($user->getKey());
            });
        }

        return $query;
    }

    public static function getRelations(): array
    {
        $panelId = Filament::getCurrentPanel()?->getId();

        if ($panelId === 'picsekolah') {
            return [
                'students' => StudentsRelationManager::class,
            ];
        }

        return [
            'classes' => ClassesRelationManager::class,
            'students' => StudentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEvents::route('/'),
            'create' => CreateEvent::route('/create'),
            'view' => ViewEvent::route('/{record}'),
            'edit' => EditEvent::route('/{record}/edit'),
        ];
    }
}
