<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventGateAssignment extends Model
{
    protected $fillable = [
        'event_gate_id',
        'user_id',
    ];
}
