<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventSetting extends Model
{
    protected $fillable = [
        'event_id',
        'ticket_code_prefix',
        'ticket_sequence_start',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'ticket_sequence_start' => 'integer',
            'settings' => 'array',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
