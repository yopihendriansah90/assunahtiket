<?php

namespace App\Filament\Resources\AttendanceReports;

use App\Filament\Resources\AttendanceReports\Pages\ListAttendanceReports;
use App\Filament\Resources\AttendanceReports\Tables\AttendanceReportsTable;
use App\Models\Student;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class AttendanceReportResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBarSquare;

    protected static ?string $navigationLabel = 'Laporan Kehadiran';

    protected static string|UnitEnum|null $navigationGroup = 'Laporan';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'Laporan Kehadiran';

    protected static ?string $pluralModelLabel = 'Laporan Kehadiran';

    public static function table(Table $table): Table
    {
        return AttendanceReportsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if (! $user || $user->can('ViewAny:ScanAttempt') === false) {
            return $query->whereRaw('1 = 0');
        }

        return $query;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Filament::getCurrentPanel()?->getId() !== 'picsekolah';
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAttendanceReports::route('/'),
        ];
    }
}
