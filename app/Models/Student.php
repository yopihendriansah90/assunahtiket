<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Validation\ValidationException;

class Student extends Model
{
    public const GENDER_MALE = 'male';

    public const GENDER_FEMALE = 'female';

    protected $fillable = [
        'event_id',
        'class_id',
        'name',
        'gender',
        'mother_name',
        'created_by',
        'updated_by',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $student): void {
            $student->created_by ??= auth()->id();
            $student->updated_by ??= auth()->id();
            static::ensureEventIsMutable($student);
        });

        static::updating(function (self $student): void {
            $student->updated_by = auth()->id();
            static::ensureEventIsMutable($student);
        });

        static::deleting(function (self $student): void {
            static::ensureEventIsMutable($student);
        });

        static::saving(function (self $student): void {
            static::ensureEventIsMutable($student);

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

    protected static function ensureEventIsMutable(self $student): void
    {
        if (static::canBypassEventLock()) {
            return;
        }

        if (blank($student->event_id)) {
            return;
        }

        if (! static::isEventLocked($student->event_id)) {
            return;
        }

        throw ValidationException::withMessages([
            'event_id' => 'Data siswa pada acara yang sudah dikunci tidak dapat diubah.',
        ]);
    }

    protected static function canBypassEventLock(): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }

    public static function isEventLocked(int|string|null $eventId): bool
    {
        if (blank($eventId)) {
            return false;
        }

        return Event::query()
            ->whereKey($eventId)
            ->whereNotNull('locked_at')
            ->exists();
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

    /**
     * @return array<string, string>
     */
    public static function genderOptions(): array
    {
        return [
            self::GENDER_MALE => 'Laki-laki',
            self::GENDER_FEMALE => 'Perempuan',
        ];
    }

    public static function genderLabel(?string $gender): string
    {
        return self::genderOptions()[$gender] ?? '-';
    }

    public function getGenderLabelAttribute(): string
    {
        return self::genderLabel($this->gender);
    }
}
