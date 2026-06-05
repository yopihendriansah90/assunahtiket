<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Ticket extends Model
{
    private const QR_SEQUENCE_PADDING = 5;

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
        return sprintf('tickets/qr/%s/%s', $this->event_id, $this->qrFileName());
    }

    public function qrDownloadFileName(): string
    {
        return $this->qrFileName();
    }

    public function qrFileName(): string
    {
        $this->loadMissing(['event', 'student.eventClass']);

        $segments = [
            $this->cleanSegment($this->event?->name ?? 'event'),
            $this->cleanSegment($this->student?->name ?? 'siswa'),
            $this->cleanSegment($this->student?->eventClass?->name ?? 'kelas'),
            $this->qrSequenceNumber(),
        ];

        return collect($segments)
            ->filter()
            ->implode('_') . '.png';
    }

    protected function qrSequenceNumber(): string
    {
        if (blank($this->ticket_code)) {
            return str_pad('0', self::QR_SEQUENCE_PADDING, '0', STR_PAD_LEFT);
        }

        if (preg_match('/(\d+)$/', (string) $this->ticket_code, $matches) === 1) {
            return str_pad($matches[1], self::QR_SEQUENCE_PADDING, '0', STR_PAD_LEFT);
        }

        return str_pad('0', self::QR_SEQUENCE_PADDING, '0', STR_PAD_LEFT);
    }

    protected function cleanSegment(?string $value): string
    {
        $segment = Str::of((string) $value)
            ->ascii()
            ->replaceMatches('/[^A-Za-z0-9]+/', '_')
            ->trim('_')
            ->upper()
            ->toString();

        return $segment !== '' ? $segment : 'X';
    }
}
