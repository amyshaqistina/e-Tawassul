<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('donation', function (Blueprint $table) {
            // Donor-supplied bank reference number (from their transfer
            // confirmation slip). Optional. Used for audit / reconciliation
            // when admin wants to match a donation row to the bank
            // statement. Long enough to fit any bank's reference format.
            if (!Schema::hasColumn('donation', 'transfer_reference')) {
                $table->string('transfer_reference', 100)->nullable()
                      ->after('support_message');
            }

            // Provenance — was this row created by a donor submitting the
            // public form, or by an admin recording an off-platform
            // donation (cash, walk-in, bank-statement reconciliation)?
            //   donor = donor submitted via the donate page form
            //   admin = admin recorded manually via /admin/donations/create
            // Default 'donor' matches existing behaviour for legacy rows.
            if (!Schema::hasColumn('donation', 'recorded_by')) {
                $table->string('recorded_by', 20)->default('donor')
                      ->after('transfer_reference');
            }

            // Admin's note when manually recording a donation — typically
            // explains why this row exists outside the normal donate page
            // flow (e.g. "Walk-in donor, cash, paper receipt #A0023").
            // Only set for recorded_by = 'admin' rows.
            if (!Schema::hasColumn('donation', 'admin_note')) {
                $table->text('admin_note')->nullable()->after('recorded_by');
            }

            // The admin who recorded a manual donation, for audit.
            if (!Schema::hasColumn('donation', 'recorded_by_admin_id')) {
                $table->unsignedBigInteger('recorded_by_admin_id')
                      ->nullable()->after('admin_note');
            }
        });
    }

    public function down(): void
    {
        Schema::table('donation', function (Blueprint $table) {
            foreach (['transfer_reference', 'recorded_by', 'admin_note', 'recorded_by_admin_id'] as $c) {
                if (Schema::hasColumn('donation', $c)) {
                    $table->dropColumn($c);
                }
            }
        });
    }
};
