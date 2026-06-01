<?php

namespace App\Filament\Resources\Users\RelationManagers;

use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class AssignedClassesRelationManager extends RelationManager
{
    protected static string $relationship = 'assignedClasses';
    protected static ?string $title = 'Kelas Terkait';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return auth()->user()?->can('Update:User') ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('event.name')
                    ->label('Acara')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('name')
                    ->label('Nama Kelas')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('code')
                    ->label('Kode Kelas')
                    ->toggleable(),
                TextColumn::make('sort_order')
                    ->label('Urutan')
                    ->sortable(),
            ])
            ->headerActions([
                AttachAction::make()
                    ->label('Hubungkan Kelas')
                    ->modalHeading('Hubungkan Kelas ke Akun PIC Sekolah')
                    ->modalSubmitActionLabel('Simpan Hubungan')
                    ->recordSelectSearchColumns(['name', 'code'])
                    ->recordSelectOptionsQuery(function (Builder $query): Builder {
                        return $query
                            ->with('event')
                            ->orderBy('event_id')
                            ->orderBy('sort_order')
                            ->orderBy('name');
                    })
                    ->multiple()
                    ->preloadRecordSelect(),
            ])
            ->recordActions([
                DetachAction::make()->label('Lepas'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make()->label('Lepas Terpilih'),
                ]),
            ]);
    }
}
