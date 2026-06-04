<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Events\EventResource;
use App\Models\Event;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class PicSekolahEventsTableWidget extends TableWidget
{
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $user = auth()->user();

        return $table
            ->query(
                Event::query()
                    ->where(function (Builder $query) use ($user): void {
                        $query
                            ->whereHas('assignedUsers', fn (Builder $assignedUsersQuery): Builder => $assignedUsersQuery->whereKey($user?->getKey()))
                            ->orWhereHas('classes.assignedUsers', fn (Builder $assignedClassUsersQuery): Builder => $assignedClassUsersQuery->whereKey($user?->getKey()));
                    })
                    ->withCount([
                        'classes as assigned_classes_count' => fn (Builder $query): Builder => $query
                            ->whereHas('assignedUsers', fn (Builder $assignedUsersQuery): Builder => $assignedUsersQuery->whereKey($user?->getKey())),
                    ])
                    ->orderBy('event_date')
            )
            ->heading('Acara yang Dipegang')
            ->description('Daftar acara yang terhubung ke akun PIC Anda beserta status lock dan jumlah kelas yang dipegang.')
            ->columns([
                TextColumn::make('index')
                    ->label('No.')
                    ->rowIndex(),
                TextColumn::make('name')
                    ->label('Nama Acara')
                    ->searchable(),
                TextColumn::make('code')
                    ->label('Kode')
                    ->badge(),
                TextColumn::make('event_date')
                    ->label('Tanggal')
                    ->date(),
                TextColumn::make('assigned_classes_count')
                    ->label('Kelas Saya'),
                TextColumn::make('locked_at')
                    ->label('Status Lock')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => filled($state) ? 'Terkunci' : 'Terbuka')
                    ->color(fn ($state): string => filled($state) ? 'warning' : 'success'),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Buka Acara')
                    ->url(fn (Event $record): string => EventResource::getUrl('view', ['record' => $record, 'relation' => 'students'], panel: 'picsekolah')),
            ])
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(5);
    }
}
