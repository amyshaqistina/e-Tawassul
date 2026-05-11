<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Blockchain extends Model
{
    use HasFactory;

    protected $table = 'blockchain';
    protected $primaryKey = 'blockchain_id';

    protected $fillable = [
        'data_from', 'data_type', 'stored_data', 'hash_type', 'verified',
        'timestamp', 'tx_hash', 'mode', 'reference_table', 'reference_id',
        'payload_meta',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
        'verified' => 'boolean',
        'payload_meta' => 'array',
    ];
}
