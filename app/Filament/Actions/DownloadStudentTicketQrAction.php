<?php

namespace App\Filament\Actions;

use App\Models\Student;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;

class DownloadStudentTicketQrAction
{
    public static function make(): Action
    {
        return Action::make('downloadStudentTicketQr')
            ->label('QR JPG')
            ->icon(Heroicon::OutlinedQrCode)
            ->color('gray')
            ->url(fn (Student $record): string => route('students.ticket-qr.download', $record))
            ->visible(fn (): bool => auth()->user()?->can('Update:Student') || auth()->user()?->hasRole('super_admin'));
    }
}
