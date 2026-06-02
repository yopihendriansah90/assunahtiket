<?php

namespace App\Filament\Resources\EventGates\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class EventGateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('event_id')
                    ->label('Acara')
                    ->relationship('event', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('assignedUsers')
                    ->label('Gate Officer')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->relationship(
                        name: 'assignedUsers',
                        titleAttribute: 'name',
                        modifyQueryUsing: function (Builder $query): Builder {
                            return $query
                                ->whereHas('roles', function (Builder $roleQuery): Builder {
                                    return $roleQuery->where('name', 'checkin_officer');
                                })
                                ->orderBy('name');
                        },
                    )
                    ->helperText('Pilih user yang bertugas scan QR pada gate ini.'),
                TextInput::make('name')
                    ->label('Nama Gerbang')
                    ->required()
                    ->maxLength(255),
                Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),
            ]);
    }
}
