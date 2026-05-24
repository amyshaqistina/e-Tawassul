<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds donation control fields to the crisis table so admin can:
 *  - Open / close the public donation page per case (donation_open)
 *  - Auto-close the page when the donation_target cap is reached
 *    (auto_close_on_target)
 *  - Audit when / why a donation was closed
 *    (donation_closed_at, donation_closed_reason)
 *
 * The donation_target column already exists on the crisis table (from
 * 2024_01_01_000007_create_crisis_table.php), so we only add the new
 * control flags here.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crisis', function (Blueprint $table) {
            $table->boolean('donation_open')->default(true)->after('donation_raised');
            $table->boolean('auto_close_on_target')->default(true)->after('donation_open');
            $table->timestamp('donation_closed_at')->nullable()->after('auto_close_on_target');
            $table->string('donation_closed_reason', 200)->nullable()->after('donation_closed_at');
        });
    }

    public function down(): void
    {
        Schema::table('crisis', function (Blueprint $table) {
            $table->dropColumn([
                'donation_open',
                'auto_close_on_target',
                'donation_closed_at',
                'donation_closed_reason',
            ]);
        });
    }
};
