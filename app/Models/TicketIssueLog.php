<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketIssueLog extends Model
{
    protected $fillable = [
        'ticket_issue_id',
        'user_id',
        'action',
        'notes',
        'old_data',
        'new_data',
    ];

    protected function casts(): array
    {
        return [
            'old_data' => 'array',
            'new_data' => 'array',
        ];
    }
}
