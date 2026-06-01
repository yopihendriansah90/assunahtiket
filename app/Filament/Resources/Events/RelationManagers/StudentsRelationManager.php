<?php

namespace App\Filament\Resources\Events\RelationManagers;

use App\Models\Student;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class StudentsRelationManager extends RelationManager
{
    protected static string $relationship = 'students';
    protected static ?string $title = 'Siswa';

    private static function statusOptions(): array
    {
        return [
            'draft' => 'Draf',
            'ready' => 'Siap',
        ];
    }

    private function isOwnerEventLocked(): bool
    {
        return filled($this->getOwnerRecord()->locked_at);
    }

    private function canBypassEventLock(): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        return $user->can('ViewAny:Student')
            || $user->can('View:Student')
            || $user->can('Create:Student')
            || $user->can('Update:Student')
            || $user->can('Delete:Student')
            || $user->can('DeleteAny:Student');
    }

    public function form(Schema $schema): Schema
    {
        $isLocked = $this->isOwnerEventLocked();
        $canBypassLock = $this->canBypassEventLock();

        return $schema
            ->components([
                Select::make('class_id')
                    ->label('Kelas')
                    ->relationship(
                        name: 'eventClass',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (Builder $query) => $query
                            ->where('event_id', $this->getOwnerRecord()->id)
                            ->orderBy('sort_order')
                            ->orderBy('name'),
                    )
                    ->required()
                    ->searchable()
                    ->preload()
                    ->disabled($isLocked && ! $canBypassLock),
                TextInput::make('name')
                    ->label('Nama Siswa')
                    ->required()
                    ->maxLength(255)
                    ->disabled($isLocked && ! $canBypassLock)
                    ->rule(function (Get $get, ?Student $record): callable {
                        return function (string $attribute, mixed $value, \Closure $fail) use ($get, $record): void {
                            $eventId = $this->getOwnerRecord()->id;
                            $classId = $get('class_id');
                            $name = mb_strtolower(trim((string) $value));
                            $motherName = mb_strtolower(trim((string) $get('mother_name')));

                            if ($name === '' || $motherName === '' || blank($classId)) {
                                return;
                            }

                            if (Student::hasDuplicateIdentity(
                                eventId: $eventId,
                                classId: $classId,
                                name: $name,
                                motherName: $motherName,
                                ignoreId: $record?->getKey(),
                            )) {
                                $fail('Kombinasi nama siswa dan nama ibu kandung sudah terdaftar pada acara ini.');
                            }
                        };
                    }),
                Select::make('gender')
                    ->label('Jenis Kelamin')
                    ->options([
                        'male' => 'Laki-laki',
                        'female' => 'Perempuan',
                    ])
                    ->required()
                    ->disabled($isLocked && ! $canBypassLock),
                Select::make('status')
                    ->label('Status')
                    ->options(self::statusOptions())
                    ->required()
                    ->default('draft')
                    ->disabled($isLocked && ! $canBypassLock),
                TextInput::make('mother_name')
                    ->label('Nama Ibu Kandung')
                    ->required()
                    ->maxLength(255)
                    ->disabled($isLocked && ! $canBypassLock)
                    ->rule(function (Get $get, ?Student $record): callable {
                        return function (string $attribute, mixed $value, \Closure $fail) use ($get, $record): void {
                            $eventId = $this->getOwnerRecord()->id;
                            $classId = $get('class_id');
                            $name = mb_strtolower(trim((string) $get('name')));
                            $motherName = mb_strtolower(trim((string) $value));

                            if ($name === '' || $motherName === '' || blank($classId)) {
                                return;
                            }

                            if (Student::hasDuplicateIdentity(
                                eventId: $eventId,
                                classId: $classId,
                                name: $name,
                                motherName: $motherName,
                                ignoreId: $record?->getKey(),
                            )) {
                                $fail('Kombinasi nama siswa dan nama ibu kandung sudah terdaftar pada acara ini.');
                            }
                        };
                    }),
            ]);
    }

    public function table(Table $table): Table
    {
        $user = auth()->user();
        $isLocked = $this->isOwnerEventLocked();
        $canBypassLock = $this->canBypassEventLock();

        return $table
            ->modifyQueryUsing(function (Builder $query) use ($user): Builder {
                if (! $user || $user->can('ViewAny:Student') === false) {
                    return $query->whereRaw('1 = 0');
                }

                return $query;
            })
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('index')
                    ->label('No.')
                    ->rowIndex(),
                TextColumn::make('eventClass.name')
                    ->label('Kelas')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('name')
                    ->label('Nama Siswa')
                    ->searchable(),
                TextColumn::make('gender')
                    ->label('Jenis Kelamin')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'male' => 'Laki-laki',
                        'female' => 'Perempuan',
                        default => $state,
                    })
                    ->badge(),
                SelectColumn::make('status')
                    ->label('Status')
                    ->options(self::statusOptions())
                    ->native(false)
                    ->selectablePlaceholder(false)
                    ->disabled($isLocked && ! $canBypassLock),
                TextColumn::make('mother_name')
                    ->label('Nama Ibu Kandung')
                    ->toggleable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                ...(
                    $isLocked && ! $canBypassLock
                        ? []
                        : [
                            CreateAction::make()
                                ->label('Tambah Siswa')
                                ->successNotificationTitle('Data siswa berhasil disimpan.'),
                        ]
                ),
            ])
            ->recordActions([
                ...(
                    $isLocked && ! $canBypassLock
                        ? []
                        : [
                            EditAction::make()
                                ->label('Ubah')
                                ->successNotificationTitle('Data siswa berhasil diperbarui.'),
                            DeleteAction::make()
                                ->label('Hapus')
                                ->successNotificationTitle('Data siswa berhasil dihapus.'),
                        ]
                ),
            ])
            ->toolbarActions([
                ...(
                    $isLocked && ! $canBypassLock
                        ? []
                        : [
                            BulkActionGroup::make([
                                DeleteBulkAction::make()
                                    ->label('Hapus Terpilih')
                                    ->successNotificationTitle('Data siswa berhasil dihapus.'),
                            ]),
                        ]
                ),
            ]);
    }
}
