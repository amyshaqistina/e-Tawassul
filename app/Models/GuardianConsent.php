<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GuardianConsent extends Model
{
    use HasFactory;

    protected $table = 'guardian_consent';
    protected $primaryKey = 'consent_id';

    protected $fillable = [
        'student_id', 'guardian_id', 'access_granted',
        'emergency_contact_verified', 'consent_date', 'expiry_date',
    ];

    protected $casts = [
        'access_granted' => 'boolean',
        'emergency_contact_verified' => 'boolean',
        'consent_date' => 'datetime',
        'expiry_date' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    public function guardian()
    {
        return $this->belongsTo(NextOfKin::class, 'guardian_id', 'nok_id');
    }
}
