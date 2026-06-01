<?php

namespace App\Filament\Resources\Events\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Acara')
                    ->description('Data dasar acara sekolah yang akan digunakan di seluruh alur tiket.')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Acara')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('code')
                            ->label('Kode Acara')
                            ->disabled()
                            ->dehydrated()
                            ->helperText('Kode dibuat otomatis dan dijamin unik.'),
                        DatePicker::make('event_date')
                            ->label('Tanggal Acara')
                            ->required(),
                        TextInput::make('location')
                            ->label('Lokasi')
                            ->maxLength(255),
                        Select::make('status')
                            ->options([
                                'draft' => 'Draf',
                                'locked' => 'Terkunci',
                                'active' => 'Aktif',
                                'archived' => 'Arsip',
                            ])
                            ->label('Status')
                            ->required()
                            ->default('draft'),
                    ])
                    ->columnSpanFull()
                    ->columns(2),
            ]);
    }
}
