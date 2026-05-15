<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Extends the `ldms.media_type` enum to include 'document' and 'video'
 * to match the new options offered in the LDMS create/edit form
 * (PDF/Word documents and MP4/WEBM video).
 *
 * Uses a raw ALTER because changing ENUM values is not supported by
 * Laravel's schema builder.  Works on MySQL/MariaDB.  For SQLite (tests)
 * the column is stored as TEXT so no migration step is needed; we just
 * no-op gracefully.
 */
return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("
                ALTER TABLE `ldms`
                MODIFY COLUMN `media_type`
                ENUM('text', 'image', 'audio', 'document', 'video', 'mixed')
                NOT NULL DEFAULT 'text'
            ");
        }
        // SQLite / PostgreSQL fall through:
        // - SQLite stores enums as TEXT, no constraint to update.
        // - PostgreSQL would need a different syntax; add here if used.
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            // Reverting: any rows currently holding 'document' or 'video'
            // will be coerced to 'mixed' to avoid data loss / fatal errors.
            DB::statement("UPDATE `ldms` SET `media_type` = 'mixed' WHERE `media_type` IN ('document', 'video')");

            DB::statement("
                ALTER TABLE `ldms`
                MODIFY COLUMN `media_type`
                ENUM('text', 'image', 'audio', 'mixed')
                NOT NULL DEFAULT 'text'
            ");
        }
    }
};
