<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Events\EventResource;
use App\Models\EventClass;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class PicSekolahClassesTableWidget extends TableWidget
{
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $user = auth()->user();

        return $table
            ->query(
                EventClass::query()
                    ->whereHas('assignedUsers', fn ($query) => $query->whereKey($user?->getKey()))
                    ->with(['event'])
                    ->withCount('students')
                    ->orderBy('event_id')
                    ->orderBy('sort_order')
                    ->orderBy('name')
            )
            ->heading('Kelas yang Dipegang')
            ->description('Jumlah siswa per kelas yang saat ini menjadi tanggung jawab Anda.')
            ->columns([
                TextColumn::make('index')
                    ->label('No.')
                    ->rowIndex(),
                TextColumn::make('event.name')
                    ->label('Acara')
                    ->searchable(),
                TextColumn::make('name')
                    ->label('Kelas')
                    ->badge(),
                TextColumn::make('students_count')
                    ->label('Jumlah Siswa')
                    ->numeric(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Buka Siswa')
                    ->url(fn (EventClass $record): string => EventResource::getUrl('view', ['record' => $record->event, 'relation' => 'students'], panel: 'picsekolah')),
            ])
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(10);
    }
}
