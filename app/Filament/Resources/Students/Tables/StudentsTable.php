<?php

namespace App\Filament\Resources\Students\Tables;

use App\Models\Student;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StudentsTable
{
    public static function configure(Table $table): Table
    {
        $user = auth()->user();
        $canBypassLock = $user?->hasRole('super_admin') ?? false;

        return $table
            ->modifyQueryUsing(function ($query) use ($user) {
                if (! $user || $user->can('ViewAny:Student') === false) {
                    return $query->whereRaw('1 = 0');
                }

                return $query;
            })
            ->columns([
                TextColumn::make('index')
                    ->label('No.')
                    ->rowIndex(),
                TextColumn::make('event.name')
                    ->label('Acara')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('eventClass.name')
                    ->label('Kelas')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('name')
                    ->label('Nama Siswa')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('mother_name')
                    ->label('Nama Ibu Kandung')
                    ->toggleable(),
                TextColumn::make('createdBy.name')
                    ->label('Diinput Oleh')
                    ->toggleable(),
                TextColumn::make('updatedBy.name')
                    ->label('Diubah Oleh')
                    ->toggleable(),
                TextColumn::make('gender')
                    ->badge()
                    ->label('Jenis Kelamin')
                    ->toggleable(),
                TextColumn::make('status')
                    ->badge()
                    ->label('Status')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('event_id')
                    ->label('Acara')
                    ->relationship('event', 'name')
                    ->searchable()
                    ->preload()
                    ->query(function (Builder $query, array $data) use ($user): Builder {
                        if (! $user || $user->can('ViewAny:Student') === false) {
                            return $query->whereRaw('1 = 0');
                        }

                        $value = $data['value'] ?? null;

                        if (blank($value)) {
                            return $query;
                        }

                        return $query->where('event_id', $value);
                    }),
                SelectFilter::make('class_id')
                    ->label('Kelas')
                    ->relationship('eventClass', 'name')
                    ->searchable()
                    ->preload()
                    ->query(function (Builder $query, array $data) use ($user): Builder {
                        if (! $user || $user->can('ViewAny:Student') === false) {
                            return $query->whereRaw('1 = 0');
                        }

                        $value = $data['value'] ?? null;

                        if (blank($value)) {
                            return $query;
                        }

                        return $query->where('class_id', $value);
                    }),
                SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draf',
                        'ready' => 'Siap',
                    ]),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Ubah')
                    ->visible(fn (Student $record): bool => ! $record->event?->isLocked() || $canBypassLock),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Hapus Terpilih'),
                ]),
            ]);
    }
}
