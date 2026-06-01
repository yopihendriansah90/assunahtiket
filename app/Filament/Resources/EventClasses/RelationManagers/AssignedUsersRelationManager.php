<?php

namespace App\Filament\Resources\EventClasses\RelationManagers;

use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AssignedUsersRelationManager extends RelationManager
{
    protected static string $relationship = 'assignedUsers';
    protected static ?string $title = 'PIC / Guru';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Nama User')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->toggleable(),
            ])
            ->headerActions([
                AttachAction::make()
                    ->label('Assign PIC / Guru')
                    ->modalHeading('Assign PIC / Guru ke Kelas')
                    ->modalSubmitActionLabel('Simpan Assign')
                    ->recordSelectSearchColumns(['name', 'email'])
                    ->multiple()
                    ->preloadRecordSelect(),
            ])
            ->recordActions([
                DetachAction::make()->label('Lepas'),
                EditAction::make()->label('Ubah'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make()->label('Lepas Terpilih'),
                ]),
            ]);
    }
}
