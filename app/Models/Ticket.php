<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Ticket extends Model
{
    protected $fillable = [
        'event_id',
        'student_id',
        'ticket_code',
        'qr_token',
        'status',
        'generated_at',
        'revoked_at',
        'generated_by',
        'revoked_by',
    ];

    protected function casts(): array
    {
        return [
            'generated_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $ticket): void {
            $ticket->status ??= 'active';
            $ticket->generated_at ??= now();
        });
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(TicketFile::class);
    }

    public function qrFile(): HasOne
    {
        return $this->hasOne(TicketFile::class)->where('type', 'qr');
    }

    public function checkins(): HasMany
    {
        return $this->hasMany(Checkin::class);
    }

    public function latestCheckin(): HasOne
    {
        return $this->hasOne(Checkin::class)->latestOfMany('checked_in_at');
    }

    public function qrPayload(): string
    {
        return $this->qr_token;
    }

    public function qrFilePath(): string
    {
        return sprintf('tickets/qr/%s/%s.jpg', $this->event_id, $this->ticket_code);
    }

    public function qrDownloadFileName(): string
    {
        $this->loadMissing(['event', 'student.eventClass']);

        $segments = [
            $this->event?->name ?? 'event',
            $this->student?->name ?? 'siswa',
            $this->student?->eventClass?->name ?? 'kelas',
            $this->ticket_code ?? 'unik',
        ];

        $filename = collect([
            Str::of($segments[0])->headline()->upper()->replace(' ', '_')->toString(),
            Str::of($segments[1])->headline()->upper()->replace(' ', '_')->toString(),
            Str::of($segments[2])->headline()->upper()->replace(' ', '_')->toString(),
        ])
            ->filter()
            ->implode('_');

        return ($filename !== '' ? $filename : 'qr-tiket') . '.jpg';
    }
}
