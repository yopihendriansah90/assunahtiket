<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScanAttempt extends Model
{
    protected $fillable = [
        'event_id',
        'ticket_id',
        'event_gate_id',
        'user_id',
        'query',
        'status',
        'scan_method',
        'student_name',
        'class_name',
        'ticket_code',
        'scanned_at',
    ];

    protected function casts(): array
    {
        return [
            'scanned_at' => 'datetime',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function gate(): BelongsTo
    {
        return $this->belongsTo(EventGate::class, 'event_gate_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
