<?php

namespace App\Filament\Resources\Checkins\Tables;

use App\Services\Checkins\CheckinExcelExportService;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\BulkAction;
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

        return $table
            ->modifyQueryUsing(function (Builder $query) use ($user): Builder {
                if (! $user || $user->can('ViewAny:Checkin') === false) {
                    return $query->whereRaw('1 = 0');
                }

                return $query
                    ->with([
                        'event',
                        'gate',
                        'user',
                        'ticket.student.eventClass',
                    ]);
            })
            ->defaultSort('checked_in_at', 'desc')
            ->columns([
                TextColumn::make('id')
                    ->label('No.')
                    ->rowIndex()
                    ->sortable(),
                TextColumn::make('event.name')
                    ->label('Acara')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('ticket.student.name')
                    ->label('Nama Siswa')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('ticket.student.eventClass.name')
                    ->label('Kelas')
                    ->toggleable()
                    ->searchable(),
                TextColumn::make('ticket.ticket_code')
                    ->label('Kode Tiket')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('gate.name')
                    ->label('Gerbang')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('user.name')
                    ->label('Operator')
                    ->toggleable(),
                TextColumn::make('scan_method')
                    ->label('Metode')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => $state ? strtoupper($state) : '-')
                    ->sortable(),
                TextColumn::make('checked_in_at')
                    ->label('Waktu Check-in')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('event_id')
                    ->label('Acara')
                    ->relationship('event', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('event_gate_id')
                    ->label('Gerbang')
                    ->relationship('gate', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('scan_method')
                    ->label('Metode')
                    ->options([
                        'qr' => 'QR',
                        'manual' => 'Manual',
                    ]),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Detail')
                    ->visible(fn (): bool => auth()->user()?->can('View:Checkin') ?? false),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('downloadExcel')
                        ->label('Download Excel')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('gray')
                        ->requiresConfirmation()
                        ->modalHeading('Download Excel')
                        ->modalSubmitActionLabel('Unduh Excel')
                        ->visible(fn (): bool => auth()->user()?->can('ViewAny:Checkin') ?? false)
                        ->action(function (BulkAction $action) {
                            $user = auth()->user();

                            if ($user === null) {
                                abort(403);
                            }

                            $selectedRecords = $action->getSelectedRecords();

                            $fileBaseName = 'checkin-' . now()->format('Ymd-His');
                            $temporaryPath = app(CheckinExcelExportService::class)
                                ->exportToTemporaryFile($selectedRecords, $fileBaseName);

                            return app(CheckinExcelExportService::class)
                                ->downloadFile($temporaryPath, strtoupper(str_replace('-', '_', $fileBaseName)) . '.xlsx');
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }
}
