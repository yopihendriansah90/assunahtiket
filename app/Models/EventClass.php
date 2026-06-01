<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class EventClass extends Model
{
    protected $table = 'classes';

    protected $fillable = [
        'event_id',
        'name',
        'code',
        'sort_order',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $eventClass): void {
            if (blank($eventClass->sort_order)) {
                $eventClass->sort_order = static::nextSortOrder($eventClass->event_id);
            }

            if (blank($eventClass->code)) {
                $eventClass->code = static::generateCode($eventClass->event_id, $eventClass->sort_order);
            }
        });
    }

    protected static function nextSortOrder(int|string|null $eventId): int
    {
        if (blank($eventId)) {
            return 1;
        }

        return (int) static::query()
            ->where('event_id', $eventId)
            ->max('sort_order') + 1;
    }

    protected static function generateCode(int|string|null $eventId, int|string|null $sortOrder): string
    {
        $number = str_pad((string) max(1, (int) $sortOrder), 3, '0', STR_PAD_LEFT);

        return "KLS-{$eventId}-{$number}";
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'class_id');
    }

    public function assignedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'event_class_user_assignments');
    }
}
