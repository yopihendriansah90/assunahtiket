<?php

namespace App\Filament\Resources\Students\Tables;

use App\Filament\Actions\DownloadStudentTicketQrAction;
use App\Models\Student;
use App\Services\Tickets\TicketQrZipExportService;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
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
                TextColumn::make('mother_whatsapp')
                    ->label('WhatsApp Ibu')
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
                    ->formatStateUsing(fn (?string $state): string => Student::genderLabel($state))
                    ->toggleable(),
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
            ])
            ->recordActions([
                Action::make('chatMotherWhatsapp')
                    ->label('Chat WA Ibu')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->color('success')
                    ->url(fn (Student $record): ?string => $record->motherWhatsappUrl())
                    ->openUrlInNewTab()
                    ->visible(fn (Student $record): bool => filled($record->motherWhatsappUrl())),
                DownloadStudentTicketQrAction::make(),
                EditAction::make()
                    ->label('Ubah')
                    ->visible(fn (Student $record): bool => ! $record->event?->isLocked() || $canBypassLock),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('downloadQrZip')
                        ->label('Download QR ZIP')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('gray')
                        ->modalHeading('Download QR ZIP')
                        ->modalSubmitActionLabel('Unduh ZIP')
                        ->requiresConfirmation()
                        ->action(function (BulkAction $action) {
                            $user = auth()->user();

                            if ($user === null) {
                                abort(403);
                            }

                            $selectedRecords = $action->getSelectedRecords();
                            $service = app(TicketQrZipExportService::class);

                            $archiveBaseName = 'qr-tiket-selected-' . now()->format('Ymd-His');

                            return response()
                                ->download(
                                    $service->exportStudents($selectedRecords, $user, $archiveBaseName),
                                    strtoupper(str_replace('-', '_', $archiveBaseName)) . '.zip',
                                )
                                ->deleteFileAfterSend(true);
                        })
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make()->label('Hapus Terpilih'),
                ]),
            ]);
    }
}
