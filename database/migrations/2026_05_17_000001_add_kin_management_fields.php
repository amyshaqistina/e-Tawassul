<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('next_of_kin', function (Blueprint $table) {
            // Free-text postal address. Optional — only relevant for
            // formal correspondence and the LDMS delivery flow.
            if (!Schema::hasColumn('next_of_kin', 'address')) {
                $table->text('address')->nullable()->after('phone');
            }

            // Marks which kin is the "primary" emergency contact.
            // Exactly one kin per student should have is_primary=true;
            // the controllers enforce this when toggling. The first kin
            // added is automatically primary.
            if (!Schema::hasColumn('next_of_kin', 'is_primary')) {
                $table->boolean('is_primary')->default(false)->after('emergency_contact_verified');
            }

            // Who created this kin record — important for audit and
            // for showing "Added by you" vs "Added by administrator
            // on your behalf" in the student-facing UI.
            //   student  = student self-registered via profile page
            //   admin    = admin created it on their behalf (death event)
            //   imaalum  = seeded from iMaalum import (unlikely but possible)
            //   self     = the kin signed up themselves (currently not a
            //              flow, kept for future use)
            if (!Schema::hasColumn('next_of_kin', 'registered_by')) {
                $table->string('registered_by', 20)->default('student')->after('is_primary');
            }

            // When this kin record was created (denormalised from
            // created_at) — convenient for audit displays.
            if (!Schema::hasColumn('next_of_kin', 'registered_at')) {
                $table->timestamp('registered_at')->nullable()->after('registered_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('next_of_kin', function (Blueprint $table) {
            foreach (['address', 'is_primary', 'registered_by', 'registered_at'] as $c) {
                if (Schema::hasColumn('next_of_kin', $c)) {
                    $table->dropColumn($c);
                }
            }
        });
    }
};
