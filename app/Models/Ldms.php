<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ldms extends Model
{
    use HasFactory;

    protected $table = 'ldms';
    protected $primaryKey = 'ldms_id';

    protected $fillable = [
        'confirmation_id',
        'nok_id',
        'crisis_id',
        'student_id',
        'date_triggered',
        'triggered_by_kin',
        'is_released',
        'message_content',
        'media_type',
        'media_file_path',
    ];

    /**
     * Casts.
     *
     *  - message_content -> "encrypted":  Laravel transparently AES-256-CBC
     *    encrypts the value on save and decrypts it on read using APP_KEY.
     *    The raw column in the DB will be ciphertext; any code calling
     *    $ldms->message_content gets the decrypted string back.
     *
     *  - media_file_path -> "array":  stored as JSON list of relative paths
     *    on the *encrypted* filesystem disk (see config/filesystems.php).
     *
     *  - is_released / triggered_by_kin -> booleans.
     *  - date_triggered -> Carbon datetime.
     */
    protected $casts = [
        'message_content'  => 'encrypted',
        'media_file_path'  => 'array',
        'is_released'      => 'boolean',
        'triggered_by_kin' => 'boolean',
        'date_triggered'   => 'datetime',
    ];

    // -------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    public function confirmation(): BelongsTo
    {
        return $this->belongsTo(DeathConfirmation::class, 'confirmation_id', 'confirmation_id');
    }

    public function nextOfKin(): BelongsTo
    {
        return $this->belongsTo(NextOfKin::class, 'nok_id', 'nok_id');
    }

    public function crisis(): BelongsTo
    {
        return $this->belongsTo(Crisis::class, 'crisis_id', 'crisis_id');
    }
}
