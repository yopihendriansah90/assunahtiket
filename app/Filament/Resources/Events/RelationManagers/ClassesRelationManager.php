<?php

namespace App\Filament\Resources\Events\RelationManagers;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ClassesRelationManager extends RelationManager
{
    protected static string $relationship = 'classes';
    protected static ?string $title = 'Kelas';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Nama Kelas')
                ->required()
                ->maxLength(255),
            TextInput::make('sort_order')
                ->label('Urutan')
                ->numeric()
                ->minValue(0)
                ->required()
                ->default(1)
                ->helperText('Urutan dipakai untuk mengatur posisi kelas.'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                //nomor urut
                TextColumn::make('index')
                    ->label('No.')
                    ->rowIndex(),

                TextColumn::make('name')
                    ->label('Nama Kelas')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('sort_order')
                    ->label('Urutan')
                    ->sortable(),
                TextColumn::make('assignedUsers.name')
                    ->label('Guru Kelas')
                    ->badge()
                    ->separator(', ')
                    ->placeholder('-')
                    ->toggleable(),
            ])
            ->headerActions([
                CreateAction::make()->label('Tambah Kelas'),
            ])
            ->recordActions([
                Action::make('assignPic')
                    ->label('Hubungkan Guru Kelas')
                    ->icon('heroicon-o-user-plus')
                    ->modalHeading(fn ($record): string => 'Hubungkan Guru Kelas untuk ' . $record->name)
                    ->modalSubmitActionLabel('Simpan Hubungan')
                    ->form([
                        Select::make('user_ids')
                            ->label('Pilih Guru Kelas')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->helperText('Akun guru kelas yang dihubungkan di sini akan mendapatkan akses ke event dan siswa sesuai kelas yang dipegang.')
                            ->options(fn (): array => User::query()
                                ->orderBy('name')
                                ->get()
                                ->filter(fn (User $user): bool => $user->hasRole('pic_sekolah'))
                                ->pluck('name', 'id')
                                ->all()),
                    ])
                    ->fillForm(function ($record): array {
                        return [
                            'user_ids' => $record->assignedUsers()->pluck('users.id')->all(),
                        ];
                    })
                    ->action(function (array $data, $record): void {
                        $record->assignedUsers()->sync($data['user_ids'] ?? []);
                    }),
                EditAction::make()->label('Ubah'),
                DeleteAction::make()->label('Hapus'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Hapus Terpilih'),
                ]),
            ]);
    }
}
