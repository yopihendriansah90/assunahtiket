<?php

namespace App\Filament\Resources\EventGates\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class EventGatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('event.name')
                    ->label('Acara')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('name')
                    ->label('Nama Pintu Masuk')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('code')
                    ->label('Kode')
                    ->toggleable(),
                TextColumn::make('assignedUsers.name')
                    ->label('Gate Officer')
                    ->badge()
                    ->listWithLineBreaks()
                    ->toggleable(),
                IconColumn::make('is_active')
                    ->boolean()
                    ->label('Aktif')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('event_id')
                    ->label('Acara')
                    ->relationship('event', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                EditAction::make()->label('Ubah'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Hapus Terpilih'),
                ]),
            ]);
    }
}
