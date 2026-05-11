<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Donation extends Model
{
    use HasFactory;

    protected $table = 'donation';
    protected $primaryKey = 'donation_id';

    protected $fillable = [
        'crisis_id', 'user_id', 'ldms_id', 'donation_amount', 'donation_date',
        'payment_method', 'donation_target', 'donor_name', 'donor_email',
        'support_message', 'blockchain_hash',
    ];

    protected $casts = [
        'donation_date' => 'datetime',
        'donation_amount' => 'decimal:2',
    ];

    public function crisis()
    {
        return $this->belongsTo(Crisis::class, 'crisis_id', 'crisis_id');
    }

    public function publicUser()
    {
        return $this->belongsTo(PublicUser::class, 'user_id', 'user_id');
    }

    public function ldms()
    {
        return $this->belongsTo(Ldms::class, 'ldms_id', 'ldms_id');
    }
}
