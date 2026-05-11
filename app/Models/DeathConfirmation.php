<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeathConfirmation extends Model
{
    use HasFactory;

    protected $table = 'death_confirmation';
    protected $primaryKey = 'confirmation_id';

    protected $fillable = [
        'crisis_id', 'nok_id', 'student_id', 'date_triggered', 'date_confirmed',
        'verified_by_kin', 'verified_by_kin_date', 'media_file_path',
        'media_file_name', 'media_file_size', 'admin_comments', 'status',
        'blockchain_reference',
    ];

    protected $casts = [
        'date_triggered' => 'datetime',
        'date_confirmed' => 'datetime',
        'verified_by_kin_date' => 'datetime',
        'verified_by_kin' => 'boolean',
    ];

    public function crisis()
    {
        return $this->belongsTo(Crisis::class, 'crisis_id', 'crisis_id');
    }

    public function nextOfKin()
    {
        return $this->belongsTo(NextOfKin::class, 'nok_id', 'nok_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    public function ldmsMessages()
    {
        return $this->hasMany(Ldms::class, 'confirmation_id', 'confirmation_id');
    }
}
