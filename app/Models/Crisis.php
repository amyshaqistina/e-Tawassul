<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Crisis extends Model
{
    use HasFactory;

    protected $table = 'crisis';
    protected $primaryKey = 'crisis_id';

    protected $fillable = [
        'crisis_type', 'crisis_description', 'crisis_details', 'impact_level',
        'location', 'date_reported', 'status', 'donation_target',
        'donation_raised', 'student_id',
    ];

    protected $casts = [
        'date_reported' => 'datetime',
        'donation_target' => 'decimal:2',
        'donation_raised' => 'decimal:2',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    public function reports()
    {
        return $this->hasMany(CrisisReport::class, 'crisis_id', 'crisis_id');
    }

    public function deathConfirmations()
    {
        return $this->hasMany(DeathConfirmation::class, 'crisis_id', 'crisis_id');
    }

    public function ldmsMessages()
    {
        return $this->hasMany(Ldms::class, 'crisis_id', 'crisis_id');
    }

    public function donations()
    {
        return $this->hasMany(Donation::class, 'crisis_id', 'crisis_id');
    }

    public function getProgressPercentAttribute(): int
    {
        if ($this->donation_target <= 0) return 0;
        return min(100, (int) round(($this->donation_raised / $this->donation_target) * 100));
    }

    public function getPriorityColorAttribute(): string
    {
        return match($this->impact_level) {
            'critical' => 'danger',
            'high'     => 'warning',
            'medium'   => 'info',
            'low'      => 'primary',
            default    => 'secondary',
        };
    }
}
