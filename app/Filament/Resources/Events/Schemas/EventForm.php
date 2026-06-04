<?php

namespace App\Filament\Resources\Events\Schemas;

use App\Models\EventSetting;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

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
                Section::make('Pengaturan Tiket')
                    ->description('Prefix ini digunakan saat sistem membuat kode tiket otomatis.')
                    ->relationship('settings')
                    ->schema([
                        TextInput::make('ticket_code_prefix')
                            ->label('Prefix Kode Tiket')
                            ->placeholder('Contoh: GTR')
                            ->helperText('Jika diisi, kode tiket akan diawali prefix ini. Jika kosong, sistem memakai kode acara.')
                            ->live(debounce: 0)
                            ->maxLength(50),
                        TextInput::make('ticket_sequence_start')
                            ->label('Nomor Awal Tiket')
                            ->numeric()
                            ->default(1)
                            ->minValue(1)
                            ->helperText('Saat ini dipakai sebagai referensi urutan tiket acara.')
                            ->live(debounce: 0)
                            ->required(),
                        Placeholder::make('ticket_code_preview')
                            ->label('Preview Kode Tiket')
                            ->content(function (Get $get, ?EventSetting $record): string {
                                $prefix = (string) ($get('ticket_code_prefix') ?? '');
                                $prefix = filled($prefix)
                                    ? $prefix
                                    : ((string) ($get('../code') ?? $record?->event?->code ?? 'TKT'));

                                $prefix = Str::of($prefix)
                                    ->ascii()
                                    ->upper()
                                    ->replaceMatches('/[^A-Z0-9]/', '')
                                    ->toString();

                                $prefix = $prefix !== '' ? $prefix : 'TKT';

                                $sequenceStart = max(1, (int) ($get('ticket_sequence_start') ?? 1));

                                return sprintf('%s-%05d', $prefix, $sequenceStart);
                            })
                            ->helperText('Contoh kode tiket pertama yang akan dibuat untuk acara ini.'),
                    ])
                    ->columnSpanFull()
                    ->columns(2),
            ]);
    }
}
