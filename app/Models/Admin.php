<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'admins';
    protected $primaryKey = 'admin_id';

    protected $fillable = [
        'admin_name', 'email', 'role', 'active', 'ip_address', 'permissions', 'password',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'permissions' => 'array',
        'active' => 'boolean',
        'password' => 'hashed',
    ];

    public function verifiedReports()
    {
        return $this->hasMany(CrisisReport::class, 'admin_verification', 'admin_id');
    }
}
