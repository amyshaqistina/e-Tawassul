<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    protected $table = 'activity_log';
    protected $primaryKey = 'log_id';

    protected $fillable = [
        'user_type', 'user_id', 'action', 'active', 'timestamp',
        'ip_address', 'user_agent', 'action_description',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
        'active' => 'boolean',
    ];

    public static function record(string $userType, string $userId, string $action, ?string $description = null): self
    {
        return self::create([
            'user_type' => $userType,
            'user_id' => $userId,
            'action' => $action,
            'action_description' => $description,
            'ip_address' => request()->ip(),
            'user_agent' => substr((string) request()->userAgent(), 0, 500),
            'timestamp' => now(),
        ]);
    }
}
