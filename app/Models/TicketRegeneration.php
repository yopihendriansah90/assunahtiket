<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketRegeneration extends Model
{
    protected $fillable = [
        'event_id',
        'ticket_id',
        'user_id',
        'reason',
        'regenerated_at',
        'old_ticket_snapshot',
        'new_ticket_snapshot',
    ];

    protected function casts(): array
    {
        return [
            'old_ticket_snapshot' => 'array',
            'new_ticket_snapshot' => 'array',
            'regenerated_at' => 'datetime',
        ];
    }
}
