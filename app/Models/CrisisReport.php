<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CrisisReport extends Model
{
    use HasFactory;

    protected $table = 'crisis_report';
    protected $primaryKey = 'report_id';

    protected $fillable = [
        'student_id', 'crisis_id', 'report_description', 'report_status',
        'date_reported', 'admin_verification', 'verified_at', 'admin_remarks',
        'blockchain_hash', 'supporting_evidence_path',
    ];

    protected $casts = [
        'date_reported' => 'datetime',
        'verified_at' => 'datetime',
        'supporting_evidence_path' => 'array',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    public function crisis()
    {
        return $this->belongsTo(Crisis::class, 'crisis_id', 'crisis_id');
    }

    public function verifier()
    {
        return $this->belongsTo(Admin::class, 'admin_verification', 'admin_id');
    }
}
