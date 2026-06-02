<?php

namespace App\Filament\Resources\Checkins;

use App\Filament\Resources\Checkins\Pages\ViewCheckin;
use App\Filament\Resources\Checkins\Pages\ListCheckins;
use App\Filament\Resources\Checkins\Tables\CheckinsTable;
use App\Models\Checkin;
use App\Models\Student;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;
use Filament\Support\Icons\Heroicon;

class CheckinResource extends Resource
{
    protected static ?string $model = Checkin::class;
    protected static ?string $recordTitleAttribute = 'id';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;
    protected static ?string $navigationLabel = 'Kehadiran';
    protected static string|UnitEnum|null $navigationGroup = 'Operasional';
    protected static ?int $navigationSort = 2;
    protected static ?string $modelLabel = 'Kehadiran';
    protected static ?string $pluralModelLabel = 'Kehadiran';

    public static function table(Table $table): Table
    {
        return CheckinsTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informasi Kehadiran')
                ->schema([
                    TextEntry::make('event.name')
                        ->label('Acara'),
                    TextEntry::make('gate.name')
                        ->label('Gerbang'),
                    TextEntry::make('scan_method')
                        ->label('Metode Scan')
                        ->badge()
                        ->formatStateUsing(fn (?string $state): string => $state ? strtoupper($state) : '-'),
                    TextEntry::make('checked_in_at')
                        ->label('Waktu Check-in')
                        ->dateTime(),
                    TextEntry::make('user.name')
                        ->label('Operator'),
                    TextEntry::make('scan_value')
                        ->label('Nilai Scan'),
                ])
                ->columns(2),
            Section::make('Detail Tiket')
                ->schema([
                    TextEntry::make('ticket.ticket_code')
                        ->label('Kode Tiket'),
                    TextEntry::make('ticket.status')
                        ->label('Status Tiket')
                        ->badge()
                        ->formatStateUsing(fn (?string $state): string => $state ? strtoupper($state) : '-'),
                    TextEntry::make('ticket.qr_token')
                        ->label('Token QR'),
                    TextEntry::make('ticket.student.name')
                        ->label('Nama Siswa'),
                    TextEntry::make('ticket.student.eventClass.name')
                        ->label('Kelas'),
                    TextEntry::make('ticket.student.gender')
                        ->label('Jenis Kelamin')
                        ->formatStateUsing(fn (?string $state): string => Student::genderLabel($state))
                        ->badge(),
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

    public static function getPages(): array
    {
        return [
            'index' => ListCheckins::route('/'),
            'view' => ViewCheckin::route('/{record}'),
        ];
    }
}
