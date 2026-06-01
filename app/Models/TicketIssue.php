<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TicketIssue extends Model
{
    protected $fillable = [
        'event_id',
        'ticket_id',
        'student_id',
        'created_by',
        'assigned_to',
        'issue_type',
        'status',
        'description',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'resolved_at' => 'datetime',
        ];
    }

    public function logs(): HasMany
    {
        return $this->hasMany(TicketIssueLog::class);
    }
}
