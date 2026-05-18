<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds iMaalum-derived fields to students:
 *  - image_url: smartcard photo URL from iMaalum profile (used as dashboard avatar)
 *  - needs_email_confirmation: true on first auto-sync, until student confirms
 *    the auto-guessed email on the welcome screen. After confirm = false forever.
 *
 * Also makes email nullable. Reason: when iMaalum returns a profile that
 * doesn't have email, we want to insert the student first and prompt for
 * email later — without violating a NOT NULL constraint at insert time.
 * (Email is still unique; MySQL treats multiple NULLs as not-equal so this is fine.)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            // iMaalum smartcard photo URL (e.g. https://smartcard.iium.edu.my/.../2225498.jpeg)
            $table->string('image_url', 500)->nullable()->after('emergency_contact');

            // True until the student verifies the auto-guessed email on first login.
            $table->boolean('needs_email_confirmation')
                ->default(false)
                ->after('image_url');
        });

        // Make email nullable so first-time iMaalum sync can insert
        // a student even before we know their email for sure.
        // Using a raw statement because Laravel's ->change() requires doctrine/dbal.
        \Illuminate\Support\Facades\DB::statement(
            "ALTER TABLE students MODIFY email VARCHAR(255) NULL"
        );
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['image_url', 'needs_email_confirmation']);
        });

        \Illuminate\Support\Facades\DB::statement(
            "ALTER TABLE students MODIFY email VARCHAR(255) NOT NULL"
        );
    }
};
