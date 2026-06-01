<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventUserAssignment extends Model
{
    protected $fillable = [
        'event_id',
        'user_id',
        'role',
    ];
}
