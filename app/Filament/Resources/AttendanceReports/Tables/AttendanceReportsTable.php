<?php

namespace App\Filament\Resources\AttendanceReports\Tables;

use App\Models\Checkin;
use App\Models\Student;
use App\Services\Reports\AttendanceReportMaintenanceService;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AttendanceReportsTable
{
    public static function configure(Table $table): Table
    {
        $user = auth()->user();
        $canManageAttendance = $user?->hasRole('super_admin') ?? false;

        return $table
            ->modifyQueryUsing(function (Builder $query) use ($user): Builder {
                if (! $user || (! $user->hasRole('super_admin') && $user->can('ViewAny:Checkin') === false)) {
                    return $query->whereRaw('1 = 0');
                }

                return $query->with([
                    'event',
                    'eventClass',
                    'ticket.latestCheckin.gate',
                ]);
            })
            ->defaultSort('name')
            ->columns([
                TextColumn::make('index')
                    ->label('No.')
                    ->rowIndex(),
                TextColumn::make('event.name')
                    ->label('Acara')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('eventClass.name')
                    ->label('Kelas')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Nama Peserta')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('gender')
                    ->label('Jenis Kelamin')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => Student::genderLabel($state))
                    ->toggleable(),
                TextColumn::make('mother_name')
                    ->label('Nama Ibu Kandung')
                    ->toggleable(),
                TextColumn::make('mother_whatsapp')
                    ->label('WhatsApp Ibu')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('ticket.ticket_code')
                    ->label('Kode Tiket')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('attendance_status')
                    ->label('Status Kehadiran')
                    ->state(fn (Student $record): string => $record->ticket?->latestCheckin ? 'Hadir' : 'Belum Hadir')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'Hadir' ? 'success' : 'danger'),
                TextColumn::make('ticket.latestCheckin.checked_in_at')
                    ->label('Waktu Check-in')
                    ->dateTime('d/m/Y H:i:s')
                    ->placeholder('-')
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderBy(
                            Checkin::query()
                                ->select('checked_in_at')
                                ->whereIn('ticket_id', function ($ticketQuery) {
                                    $ticketQuery
                                        ->select('id')
                                        ->from('tickets')
                                        ->whereColumn('student_id', 'students.id');
                                })
                                ->latest('checked_in_at')
                                ->limit(1),
                            $direction,
                        );
                    }),
                TextColumn::make('ticket.latestCheckin.gate.name')
                    ->label('Pintu Masuk')
                    ->placeholder('-')
                    ->toggleable(),
                TextColumn::make('ticket.latestCheckin.scan_method')
                    ->label('Metode Scan')
                    ->placeholder('-')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => $state ? strtoupper($state) : '-'),
            ])
            ->filters([
                SelectFilter::make('event_id')
                    ->label('Acara')
                    ->relationship('event', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('class_id')
                    ->label('Kelas')
                    ->relationship('eventClass', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('attendance_status')
                    ->label('Status Kehadiran')
                    ->options([
                        'hadir' => 'Hadir',
                        'belum_hadir' => 'Belum Hadir',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $value = $data['value'] ?? null;

                        return match ($value) {
                            'hadir' => $query->whereHas('ticket.checkins'),
                            'belum_hadir' => $query->whereDoesntHave('ticket.checkins'),
                            default => $query,
                        };
                    }),
            ])
            ->recordActions([
                Action::make('resetCheckin')
                    ->label('Reset 1 Peserta')
                    ->icon('heroicon-o-arrow-path')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading(fn (Student $record): string => 'Reset kehadiran untuk ' . $record->name)
                    ->modalDescription(function (Student $record): string {
                        $checkinCount = $record->ticket?->checkins()->count() ?? 0;

                        return $checkinCount > 0
                            ? 'Tindakan ini akan menghapus ' . number_format($checkinCount) . ' data check-in milik peserta ini dari laporan.'
                            : 'Peserta ini belum punya data check-in yang bisa dihapus.';
                    })
                    ->visible(fn (Student $record): bool => $canManageAttendance && $record->ticket?->checkins()->exists() === true)
                    ->action(function (Student $record): void {
                        $deleted = app(AttendanceReportMaintenanceService::class)->resetAttendanceForStudent($record);

                        Notification::make()
                            ->title($deleted > 0 ? 'Kehadiran peserta berhasil direset' : 'Tidak ada data kehadiran untuk peserta ini')
                            ->success()
                            ->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('resetSelectedAttendance')
                        ->label('Reset Banyak Peserta')
                        ->icon('heroicon-o-arrow-path')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading(function (BulkAction $action): string {
                            $count = $action->getSelectedRecords()->count();

                            return 'Reset kehadiran ' . number_format($count) . ' peserta';
                        })
                        ->modalDescription(function (BulkAction $action): string {
                            $count = $action->getSelectedRecords()->count();

                            return $count > 0
                                ? 'Tindakan ini akan menghapus seluruh data check-in pada ' . number_format($count) . ' peserta yang dipilih.'
                                : 'Belum ada peserta yang dipilih.';
                        })
                        ->visible(fn (): bool => $canManageAttendance)
                        ->action(function (BulkAction $action): void {
                            $deleted = app(AttendanceReportMaintenanceService::class)->resetAttendanceForStudents(
                                $action->getSelectedRecords(),
                            );

                            Notification::make()
                                ->title($deleted > 0 ? 'Kehadiran peserta terpilih berhasil direset' : 'Tidak ada data kehadiran pada pilihan saat ini')
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->emptyStateHeading('Belum ada data laporan yang dapat ditampilkan')
            ->emptyStateDescription('Pilih filter acara untuk melihat daftar peserta hadir dan belum hadir pada event tertentu.');
    }
}
