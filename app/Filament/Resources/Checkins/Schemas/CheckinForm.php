<?php

namespace App\Filament\Resources\Checkins\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class CheckinForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('event_id')
                    ->label('Acara')
                    ->relationship(
                        name: 'event',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (Builder $query): Builder => $query->orderBy('name'),
                    )
                    ->searchable()
                    ->preload()
                    ->live()
                    ->required(),
                Select::make('ticket_id')
                    ->label('Tiket')
                    ->relationship(
                        name: 'ticket',
                        titleAttribute: 'ticket_code',
                        modifyQueryUsing: function (Builder $query, Get $get): Builder {
                            if (blank($get('event_id'))) {
                                return $query->whereRaw('1 = 0');
                            }

                            return $query
                                ->where('event_id', $get('event_id'))
                                ->orderBy('ticket_code');
                        },
                    )
                    ->searchable()
                    ->preload()
                    ->required()
                    ->helperText('Pilih tiket peserta yang akan diberi catatan kehadiran.'),
                Select::make('event_gate_id')
                    ->label('Pintu Masuk')
                    ->relationship(
                        name: 'gate',
                        titleAttribute: 'name',
                        modifyQueryUsing: function (Builder $query, Get $get): Builder {
                            if (blank($get('event_id'))) {
                                return $query->whereRaw('1 = 0');
                            }

                            return $query
                                ->where('event_id', $get('event_id'))
                                ->orderBy('name');
                        },
                    )
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('user_id')
                    ->label('Operator')
                    ->relationship(
                        name: 'user',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (Builder $query): Builder => $query->orderBy('name'),
                    )
                    ->searchable()
                    ->preload()
                    ->default(fn () => auth()->id())
                    ->required(),
                Select::make('scan_method')
                    ->label('Metode Scan')
                    ->options([
                        'qr' => 'QR',
                        'manual' => 'Manual',
                    ])
                    ->default('manual')
                    ->required(),
                TextInput::make('scan_value')
                    ->label('Nilai Scan')
                    ->maxLength(255)
                    ->helperText('Contoh: QR token atau kode tiket yang digunakan saat testing.'),
                DateTimePicker::make('checked_in_at')
                    ->label('Waktu Check-in')
                    ->seconds(false)
                    ->default(now())
                    ->required(),
            ]);
    }
}
