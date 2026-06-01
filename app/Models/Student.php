<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Validation\ValidationException;

class Student extends Model
{
    protected $fillable = [
        'event_id',
        'class_id',
        'name',
        'gender',
        'mother_name',
        'status',
        'locked_at',
        'locked_by',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'locked_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $student): void {
            $student->created_by ??= auth()->id();
            $student->updated_by ??= auth()->id();
        });

        static::updating(function (self $student): void {
            $student->updated_by = auth()->id();
        });

        static::saving(function (self $student): void {
            if (
                blank($student->event_id)
                || blank($student->class_id)
                || blank($student->name)
                || blank($student->mother_name)
            ) {
                return;
            }

            if (static::hasDuplicateIdentity(
                eventId: $student->event_id,
                classId: $student->class_id,
                name: $student->name,
                motherName: $student->mother_name,
                ignoreId: $student->exists ? $student->getKey() : null,
            )) {
                throw ValidationException::withMessages([
                    'name' => 'Data siswa dengan nama "' . $student->name . '" dan nama ibu kandung "' . $student->mother_name . '" sudah terdaftar pada acara dan kelas ini.',
                ]);
            }
        });
    }

    public static function hasDuplicateIdentity(
        int|string $eventId,
        int|string $classId,
        string $name,
        string $motherName,
        int|string|null $ignoreId = null,
    ): bool {
        return static::query()
            ->where('event_id', $eventId)
            ->where('class_id', $classId)
            ->whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower(trim($name))])
            ->whereRaw('LOWER(TRIM(mother_name)) = ?', [mb_strtolower(trim($motherName))])
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists();
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function eventClass(): BelongsTo
    {
        return $this->belongsTo(EventClass::class, 'class_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function ticket(): HasOne
    {
        return $this->hasOne(Ticket::class);
    }
}
