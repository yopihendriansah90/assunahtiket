<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class EventGate extends Model
{
    protected $fillable = [
        'event_id',
        'name',
        'code',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $eventGate): void {
            if (filled($eventGate->code)) {
                return;
            }

            $eventCode = $eventGate->event?->code
                ?? Event::query()->whereKey($eventGate->event_id)->value('code')
                ?? 'EVT';

            $base = Str::of($eventCode)
                ->ascii()
                ->upper()
                ->replaceMatches('/[^A-Z0-9]/', '')
                ->toString();

            $base = $base !== '' ? $base : 'EVT';
            $nameSegment = Str::of($eventGate->name ?: 'GATE')
                ->ascii()
                ->upper()
                ->replaceMatches('/[^A-Z0-9]/', '')
                ->substr(0, 4)
                ->toString();
            $nameSegment = $nameSegment !== '' ? $nameSegment : 'GATE';
            $sequence = 1;

            do {
                $eventGate->code = sprintf('%s-%s-%03d', $base, $nameSegment, $sequence);
                $sequence++;
            } while (
                static::query()
                    ->where('event_id', $eventGate->event_id)
                    ->where('code', $eventGate->code)
                    ->exists()
            );
        });
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function checkins(): HasMany
    {
        return $this->hasMany(Checkin::class);
    }

    public function assignedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'event_gate_assignments')->withTimestamps();
    }

    public function isAssignedTo(User $user): bool
    {
        return $this->assignedUsers()
            ->whereKey($user->getKey())
            ->exists();
    }

    public function canBeScannedBy(User $user): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        return $this->isAssignedTo($user);
    }
}
