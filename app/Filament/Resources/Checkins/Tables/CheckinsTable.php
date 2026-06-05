<?php

namespace App\Filament\Resources\Checkins\Tables;

use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CheckinsTable
{
    public static function configure(Table $table): Table
    {
        $user = auth()->user();
        $isSuperAdmin = $user?->hasRole('super_admin') ?? false;

        return $table
            ->poll('5s')
            ->modifyQueryUsing(function (Builder $query) use ($user): Builder {
                if (! $user || $user->can('ViewAny:Checkin') === false) {
                    return $query->whereRaw('1 = 0');
                }

                return $query
                    ->with([
                        'event',
                        'gate',
                        'user',
                        'ticket.student',
                    ]);
            })
            ->defaultSort('scanned_at', 'desc')
            ->columns([
                TextColumn::make('id')
                    ->label('No.')
                    ->rowIndex()
                    ->sortable(),
                TextColumn::make('scanned_at')
                    ->label('Waktu Scan')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('event.name')
                    ->label('Acara')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('student_name')
                    ->label('Nama Siswa')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('class_name')
                    ->label('Kelas')
                    ->toggleable()
                    ->searchable(),
                TextColumn::make('ticket_code')
                    ->label('Kode Tiket')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('query')
                    ->label('Input Scan')
                    ->toggleable()
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'success' => 'success',
                        'already_scanned' => 'warning',
                        'missing' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state): string => self::formatScanStatus($state))
                    ->sortable(),
                TextColumn::make('gate.name')
                    ->label('Pintu Masuk')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('user.name')
                    ->label('Operator')
                    ->toggleable(),
                TextColumn::make('scan_method')
                    ->label('Metode')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => self::formatScanMethod($state))
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('event_id')
                    ->label('Acara')
                    ->relationship('event', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('event_gate_id')
                    ->label('Pintu Masuk')
                    ->relationship('gate', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('scan_method')
                    ->label('Metode')
                    ->options([
                        'qr' => 'QR',
                        'manual' => 'Manual',
                        'unknown' => 'Tidak diketahui',
                    ]),
                SelectFilter::make('status')
                    ->label('Status Scan')
                    ->options([
                        'success' => 'Valid',
                        'missing' => 'Invalid',
                        'already_scanned' => 'Scan Ulang',
                    ]),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Detail')
                    ->visible(fn (): bool => auth()->user()?->can('View:Checkin') ?? false),
                EditAction::make()
                    ->label('Ubah')
                    ->visible(fn () => $isSuperAdmin),
                DeleteAction::make()
                    ->label('Hapus')
                    ->visible(fn () => $isSuperAdmin),
            ])
            ->toolbarActions([
                \Filament\Actions\BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Hapus Terpilih')
                        ->visible(fn () => $isSuperAdmin),
                ]),
            ]);
    }

    public static function formatScanStatus(?string $state): string
    {
        return match ($state) {
            'success' => 'Valid',
            'missing' => 'Invalid',
            'already_scanned' => 'Scan Ulang',
            default => strtoupper((string) ($state ?: '-')),
        };
    }

    public static function formatScanMethod(?string $state): string
    {
        return match ($state) {
            'qr' => 'QR',
            'manual' => 'Manual',
            null, '' => '-',
            default => ucfirst($state),
        };
    }
}
