<?php

namespace App\Filament\Resources\Checkins;

use App\Filament\Resources\Checkins\Pages\CreateCheckin;
use App\Filament\Resources\Checkins\Pages\EditCheckin;
use App\Filament\Resources\Checkins\Pages\ViewCheckin;
use App\Filament\Resources\Checkins\Pages\ListCheckins;
use App\Models\Event;
use App\Models\EventGate;
use App\Filament\Resources\Checkins\Tables\CheckinsTable;
use App\Models\ScanAttempt;
use App\Models\Student;
use App\Models\Ticket;
use App\Models\User;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;
use Filament\Support\Icons\Heroicon;

class CheckinResource extends Resource
{
    protected static ?string $model = ScanAttempt::class;
    protected static ?string $recordTitleAttribute = 'student_name';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;
    protected static ?string $navigationLabel = 'Riwayat Scan';
    protected static string|UnitEnum|null $navigationGroup = 'Operasional';
    protected static ?int $navigationSort = 2;
    protected static ?string $modelLabel = 'Riwayat Scan';
    protected static ?string $pluralModelLabel = 'Riwayat Scan';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informasi Scan')
                ->schema([
                    Select::make('event_id')
                        ->label('Acara')
                        ->relationship('event', 'name')
                        ->searchable()
                        ->preload()
                        ->exists((new Event())->getTable(), 'id'),
                    Select::make('ticket_id')
                        ->label('Tiket')
                        ->relationship('ticket', 'ticket_code')
                        ->searchable()
                        ->preload()
                        ->exists((new Ticket())->getTable(), 'id'),
                    Select::make('event_gate_id')
                        ->label('Pintu Masuk')
                        ->relationship('gate', 'name')
                        ->searchable()
                        ->preload()
                        ->exists((new EventGate())->getTable(), 'id'),
                    Select::make('user_id')
                        ->label('Operator')
                        ->relationship('user', 'name')
                        ->searchable()
                        ->preload()
                        ->exists((new User())->getTable(), 'id'),
                    Select::make('status')
                        ->label('Status Scan')
                        ->options([
                            'success' => 'Valid',
                            'missing' => 'Invalid',
                            'already_scanned' => 'Scan Ulang',
                        ])
                        ->required(),
                    Select::make('scan_method')
                        ->label('Metode Scan')
                        ->options([
                            'qr' => 'QR',
                            'manual' => 'Manual',
                        ]),
                    DateTimePicker::make('scanned_at')
                        ->label('Waktu Scan')
                        ->seconds(false)
                        ->default(now())
                        ->required(),
                    TextInput::make('query')
                        ->label('Input Scan')
                        ->maxLength(255),
                    TextInput::make('ticket_code')
                        ->label('Kode Tiket')
                        ->maxLength(255),
                    TextInput::make('student_name')
                        ->label('Nama Siswa')
                        ->maxLength(255),
                    TextInput::make('class_name')
                        ->label('Kelas')
                        ->maxLength(255),
                ])
                ->columns(2),
        ])

        ->columns(1)


        ;
    }

    public static function table(Table $table): Table
    {
        return CheckinsTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informasi Scan')
                ->schema([
                    TextEntry::make('event.name')
                        ->label('Acara'),
                    TextEntry::make('gate.name')
                        ->label('Pintu Masuk'),
                    TextEntry::make('status')
                        ->label('Status Scan')
                        ->badge()
                        ->formatStateUsing(fn (?string $state): string => CheckinsTable::formatScanStatus($state)),
                    TextEntry::make('scan_method')
                        ->label('Metode Scan')
                        ->badge()
                        ->formatStateUsing(fn (?string $state): string => CheckinsTable::formatScanMethod($state)),
                    TextEntry::make('scanned_at')
                        ->label('Waktu Scan')
                        ->dateTime(),
                    TextEntry::make('user.name')
                        ->label('Operator'),
                    TextEntry::make('query')
                        ->label('Nilai Scan'),
                ])
                ->columns(2),
            Section::make('Detail Tiket')
                ->schema([
                    TextEntry::make('ticket_code')
                        ->label('Kode Tiket'),
                    TextEntry::make('student_name')
                        ->label('Nama Siswa'),
                    TextEntry::make('class_name')
                        ->label('Kelas'),
                    TextEntry::make('ticket.student.gender')
                        ->label('Jenis Kelamin')
                        ->formatStateUsing(fn (?string $state): string => Student::genderLabel($state))
                        ->badge()
                        ->visible(fn (ScanAttempt $record): bool => $record->ticket !== null),
                ])
                ->columns(2),
        ]);
    }

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return Filament::getCurrentPanel()?->getId() === 'picsekolah'
            ? 'Sekolah'
            : 'Operasional';
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if (! $user || $user->can('ViewAny:Checkin') === false) {
            return $query->whereRaw('1 = 0');
        }

        return $query;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCheckins::route('/'),
            'create' => CreateCheckin::route('/create'),
            'edit' => EditCheckin::route('/{record}/edit'),
            'view' => ViewCheckin::route('/{record}'),
        ];
    }

    public static function getRecordTitle(?Model $record): string
    {
        if (! $record instanceof ScanAttempt) {
            return parent::getRecordTitle($record);
        }

        return $record->student_name
            ?: $record->ticket_code
            ?: 'Log Scan #' . $record->getKey();
    }
}
