<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class NextOfKin extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'next_of_kin';
    protected $primaryKey = 'nok_id';

    protected $fillable = [
        'student_id', 'first_name', 'last_name', 'relationship_to_student',
        'email', 'phone', 'access_level', 'emergency_contact_verified',
        'consent_date', 'expiry_date', 'password',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'emergency_contact_verified' => 'boolean',
        'consent_date' => 'datetime',
        'expiry_date' => 'datetime',
        'password' => 'hashed',
    ];

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    public function deathConfirmations()
    {
        return $this->hasMany(DeathConfirmation::class, 'nok_id', 'nok_id');
    }

    public function ldmsMessages()
    {
        return $this->hasMany(Ldms::class, 'nok_id', 'nok_id');
    }

    public function guardianConsents()
    {
        return $this->hasMany(GuardianConsent::class, 'guardian_id', 'nok_id');
    }
}
