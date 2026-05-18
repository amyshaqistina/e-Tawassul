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
        'image_url', 'needs_email_confirmation',
        'imaalum_synced_at', 'status', 'password',
        // Bank info — student-managed, shown to donors on verified cases
        'bank_name', 'bank_account_holder', 'bank_account_number', 'qr_code_path',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'imaalum_synced_at'        => 'datetime',
        'date_of_birth'            => 'date',
        'password'                 => 'hashed',
        'needs_email_confirmation' => 'boolean',
        // Encrypts at rest. The donor-facing donate page also masks all
        // but the last 4 digits in the rendered HTML (see view).
        'bank_account_number'      => 'encrypted',
    ];

    public function getAuthIdentifierName() { return 'student_id'; }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    /**
     * Masked account number for display.
     * "1234567890123" -> "•••• •••• 0123"
     */
    public function getBankAccountMaskedAttribute(): ?string
    {
        if (!$this->bank_account_number) return null;
        $clean = preg_replace('/\D/', '', $this->bank_account_number);
        if (strlen($clean) <= 4) return $clean;
        return '•••• •••• ' . substr($clean, -4);
    }

    /**
     * True when the student has enough info filled in for a donor
     * to make a direct transfer (bank or QR).
     */
    public function getHasDirectDonationMethodsAttribute(): bool
    {
        return (bool) ($this->bank_account_number || $this->qr_code_path);
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
