<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Student extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'students';
    protected $primaryKey = 'student_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'student_id', 'first_name', 'last_name', 'email', 'kulliyyah',
        'programme', 'year_of_study', 'mahallah', 'phone', 'gender',
        'nationality', 'date_of_birth', 'enrollment_status', 'emergency_contact',
        'imaalum_synced_at', 'status', 'password',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'imaalum_synced_at' => 'datetime',
        'date_of_birth' => 'date',
        'password' => 'hashed',
    ];

    public function getAuthIdentifierName() { return 'student_id'; }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function nextOfKin()
    {
        return $this->hasMany(NextOfKin::class, 'student_id', 'student_id');
    }

    public function crisisReports()
    {
        return $this->hasMany(CrisisReport::class, 'student_id', 'student_id');
    }

    public function crises()
    {
        return $this->hasMany(Crisis::class, 'student_id', 'student_id');
    }

    public function deathConfirmation()
    {
        return $this->hasOne(DeathConfirmation::class, 'student_id', 'student_id');
    }

    public function ldmsMessages()
    {
        return $this->hasMany(Ldms::class, 'student_id', 'student_id');
    }

    public function guardianConsents()
    {
        return $this->hasMany(GuardianConsent::class, 'student_id', 'student_id');
    }
}
