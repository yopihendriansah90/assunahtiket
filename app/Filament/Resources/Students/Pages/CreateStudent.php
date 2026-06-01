<?php

namespace App\Filament\Resources\Students\Pages;

use App\Filament\Resources\Students\StudentResource;
use App\Models\Student;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;

class CreateStudent extends CreateRecord
{
    protected static string $resource = StudentResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if ((auth()->user()?->hasRole('super_admin') ?? false) === false && Student::isEventLocked($data['event_id'] ?? null)) {
            throw ValidationException::withMessages([
                'event_id' => 'Data siswa pada acara yang sudah dikunci tidak dapat ditambahkan.',
            ]);
        }

        return $data;
    }

    protected function getCreatedNotificationMessage(): ?string
    {
        return 'Data siswa berhasil disimpan.';
    }
}
