<?php

namespace App\Filament\Resources\EventClasses\Schemas;

use App\Models\EventClass;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rule;

class EventClassForm
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
                    ->live()
                    ->required(),
                TextInput::make('name')
                    ->label('Nama Kelas')
                    ->required()
                    ->columnSpan(3)
                    ->maxLength(255)
                    ->rule(function (Get $get, ?EventClass $record): Rule {
                        $rule = Rule::unique('classes', 'name')
                            ->where(fn ($query) => $query->where('event_id', $get('event_id')));

                        return $record ? $rule->ignore($record->getKey()) : $rule;
                    })
                    ->validationMessages([
                        'unique' => 'Nama kelas sudah digunakan pada acara ini.',
                    ]),
                TextInput::make('sort_order')
                    ->label('Urutan')
                    ->numeric()
                    ->minValue(0)
                    ->required()
                    ->columnSpan(1)
                    ->default(fn (Get $get): int => blank($get('event_id'))
                        ? 1
                        : ((int) EventClass::query()
                            ->where('event_id', $get('event_id'))
                            ->max('sort_order') + 1))
                    ->helperText('Urutan bisa diisi manual. Jika kosong, sistem akan memakai urutan berikutnya.'),
            ])
            ->columns(4);
    }
}
