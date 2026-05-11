<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationLog extends Model
{
    use HasFactory;

    protected $table = 'notification_log';
    protected $primaryKey = 'notification_id';

    protected $fillable = [
        'recipient_type', 'recipient_id', 'student_id', 'notification_type',
        'subject', 'notification_message', 'link', 'timestamp', 'read_at',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
        'read_at' => 'datetime',
    ];

    public function scopeUnread($q) { return $q->whereNull('read_at'); }

    public function scopeForRecipient($q, string $type, string $id)
    {
        return $q->where('recipient_type', $type)->where('recipient_id', $id);
    }
}
