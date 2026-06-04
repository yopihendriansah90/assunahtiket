<?php

namespace App\Filament\Resources\Events\Pages;

use App\Filament\Resources\Events\EventResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Width;

class CreateEvent extends CreateRecord
{
    protected static string $resource = EventResource::class;

    public function getMaxContentWidth(): Width|string|null
    {
        return Width::Full;
    }

    protected function getRedirectUrl(): string
    {
        return EventResource::getUrl('edit', ['record' => $this->getRecord()]);
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Acara berhasil dibuat')
            ->body(sprintf(
                'Acara "%s" sudah tersimpan dan Anda tetap berada di mode edit untuk melanjutkan pengaturan data, kelas, dan tiket.',
                $this->getRecord()->name,
            ));
    }
}
