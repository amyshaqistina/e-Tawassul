<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatbotLog extends Model
{
    protected $fillable = [
        'user_id',
        'user_role',
        'language',
        'message_hash',
        'escalated',
        'escalation_reason',
    ];

    protected $casts = [
        'escalated' => 'boolean',
    ];
}
