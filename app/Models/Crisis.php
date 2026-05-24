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
        // Donation control fields (added 2026-05-24)
        'donation_open', 'auto_close_on_target',
        'donation_closed_at', 'donation_closed_reason',
    ];

    protected $casts = [
        'date_reported' => 'datetime',
        'donation_target' => 'decimal:2',
        'donation_raised' => 'decimal:2',
        // Donation control casts
        'donation_open' => 'boolean',
        'auto_close_on_target' => 'boolean',
        'donation_closed_at' => 'datetime',
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

    // ==================================================================
    // DONATION CONTROL HELPERS
    // ==================================================================

    /**
     * True when the public donate page should accept new contributions.
     * Combines the admin's manual on/off switch with the auto-close
     * behaviour (if cap reached and auto-close enabled, donations are
     * considered closed regardless of the donation_open flag).
     */
    public function isAcceptingDonations(): bool
    {
        if (!$this->donation_open) {
            return false;
        }
        if ($this->auto_close_on_target
            && $this->donation_target > 0
            && $this->donation_raised >= $this->donation_target) {
            return false;
        }
        return true;
    }

    /**
     * Returns 'goal_reached' if the cap was hit, otherwise the explicit
     * closed_reason set by the admin. Used by the public donate page to
     * choose between the celebratory and neutral closed messages.
     */
    public function getClosedKindAttribute(): string
    {
        if ($this->donation_target > 0
            && $this->donation_raised >= $this->donation_target) {
            return 'goal_reached';
        }
        return 'admin_closed';
    }
}
