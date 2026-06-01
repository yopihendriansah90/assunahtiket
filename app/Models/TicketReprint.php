<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketReprint extends Model
{
    protected $fillable = [
        'event_id',
        'ticket_id',
        'user_id',
        'reason',
        'reprinted_at',
    ];
}
