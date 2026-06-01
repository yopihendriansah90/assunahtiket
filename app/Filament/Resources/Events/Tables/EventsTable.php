<?php

namespace App\Filament\Resources\Events\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Facades\Filament;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EventsTable
{
    public static function configure(Table $table): Table
    {
        $user = auth()->user();

        return $table
            ->modifyQueryUsing(function (Builder $query) use ($user): Builder {
                if (! $user || $user->can('ViewAny:Event') === false) {
                    return $query->whereRaw('1 = 0');
                }

                return $query;
            })
            ->columns([
                TextColumn::make('id')
                    ->label('No')
                    ->rowIndex()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Nama Acara')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('code')
                    ->label('Kode')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('event_date')
                    ->label('Tanggal')
                    ->date()
                    ->sortable(),
                TextColumn::make('location')
                    ->label('Lokasi')
                    ->toggleable(),
                TextColumn::make('status')
                    ->badge()
                    ->label('Status')
                    ->sortable(),
                TextColumn::make('lockedBy.name')
                    ->label('Dikunci Oleh')
                    ->toggleable(),
                TextColumn::make('locked_at')
                    ->label('Dikunci Pada')
                    ->dateTime()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draf',
                        'locked' => 'Terkunci',
                        'active' => 'Aktif',
                        'archived' => 'Arsip',
                    ]),
            ])
            ->recordActions([
                ViewAction::make()->label('Lihat'),
                ...(
                    Filament::getCurrentPanel()?->getId() === 'picsekolah'
                        ? []
                        : [EditAction::make()->label('Ubah')]
                ),
            ])
            ->toolbarActions([
                ...(
                    Filament::getCurrentPanel()?->getId() === 'picsekolah'
                        ? []
                        : [
                            BulkActionGroup::make([
                                DeleteBulkAction::make()->label('Hapus Terpilih'),
                            ]),
                        ]
                ),
            ]);
    }
}
