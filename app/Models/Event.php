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

    protected static function booted(): void
    {
        static::creating(function (Event $event): void {
            if (filled($event->code)) {
                return;
            }

            $base = Str::of($event->name ?: 'EVENT')
                ->ascii()
                ->replaceMatches('/[^A-Za-z0-9]/', '')
                ->upper()
                ->substr(0, 6)
                ->toString();

            $base = $base !== '' ? $base : 'EVT';
            $sequence = 1;

            do {
                $event->code = sprintf('%s-%03d', $base, $sequence);
                $sequence++;
            } while (static::query()->where('code', $event->code)->exists());
        });
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
