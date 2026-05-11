<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PublicUser extends Model
{
    use HasFactory;

    protected $table = 'public_users';
    protected $primaryKey = 'user_id';

    protected $fillable = [
        'first_name', 'last_name', 'email',
        'view_public_dashboard', 'makes_donation',
    ];

    protected $casts = [
        'view_public_dashboard' => 'boolean',
        'makes_donation' => 'boolean',
    ];

    public function donations()
    {
        return $this->hasMany(Donation::class, 'user_id', 'user_id');
    }
}
