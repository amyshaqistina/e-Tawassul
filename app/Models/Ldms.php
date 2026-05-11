<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Ldms extends Model
{
    use HasFactory;

    protected $table = 'ldms';
    protected $primaryKey = 'ldms_id';

    protected $fillable = [
        'confirmation_id', 'nok_id', 'crisis_id', 'student_id', 'date_triggered',
        'triggered_by_kin', 'is_released', 'message_content', 'media_type',
        'media_file_path',
    ];

    protected $casts = [
        'date_triggered' => 'datetime',
        'triggered_by_kin' => 'boolean',
        'is_released' => 'boolean',
        'media_file_path' => 'array',
    ];

    /**
     * Auto-encrypt message_content on write, decrypt on read.
     */
    public function setMessageContentAttribute($value): void
    {
        $this->attributes['message_content'] = $value !== null ? Crypt::encryptString($value) : null;
    }

    public function getMessageContentAttribute($value): ?string
    {
        if ($value === null) return null;
        try {
            return Crypt::decryptString($value);
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    public function confirmation()
    {
        return $this->belongsTo(DeathConfirmation::class, 'confirmation_id', 'confirmation_id');
    }

    public function nextOfKin()
    {
        return $this->belongsTo(NextOfKin::class, 'nok_id', 'nok_id');
    }

    public function crisis()
    {
        return $this->belongsTo(Crisis::class, 'crisis_id', 'crisis_id');
    }
}
