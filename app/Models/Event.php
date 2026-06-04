<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Event extends Model
{
    protected $fillable = [
        'name',
        'code',
        'event_date',
        'location',
        'status',
        'locked_at',
        'locked_by',
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'locked_at' => 'datetime',
        ];
    }

    public function isLocked(): bool
    {
        return filled($this->locked_at);
    }

    protected static function booted(): void
    {
        static::saving(function (Event $event): void {
            if (blank($event->code)) {
                return;
            }

            $event->code = static::normalizeCode($event->code);
        });

        static::creating(function (Event $event): void {
            if (filled($event->code)) {
                return;
            }

            $event->code = static::generateUniqueCode($event->name);
        });
    }

    protected static function normalizeCode(string $code): string
    {
        return Str::of($code)
            ->trim()
            ->ascii()
            ->upper()
            ->replaceMatches('/[^A-Z0-9-]/', '-')
            ->replaceMatches('/-+/', '-')
            ->trim('-')
            ->toString();
    }

    protected static function generateUniqueCode(?string $name): string
    {
        $base = Str::of($name ?: 'EVENT')
            ->ascii()
            ->replaceMatches('/[^A-Za-z0-9]/', '')
            ->upper()
            ->substr(0, 6)
            ->toString();

        $base = $base !== '' ? $base : 'EVT';
        $sequence = 1;

        do {
            $code = sprintf('%s-%03d', $base, $sequence);
            $sequence++;
        } while (static::query()->where('code', $code)->exists());

        return $code;
    }

    public function lockedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    public function assignedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'event_user_assignments')
            ->withPivot(['role'])
            ->withTimestamps();
    }

    public function settings(): HasOne
    {
        return $this->hasOne(EventSetting::class);
    }

    public function classes(): HasMany
    {
        return $this->hasMany(EventClass::class);
    }

    public function gates(): HasMany
    {
        return $this->hasMany(EventGate::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }
}
