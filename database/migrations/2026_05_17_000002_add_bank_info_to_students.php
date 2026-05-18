<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            // Bank info that donors will see on the public donate page
            // when the student's crisis case is verified. The student
            // controls these fields from their profile page; they're
            // optional — if blank, donors only see the platform escrow
            // / FPX path on the donate page.

            if (!Schema::hasColumn('students', 'bank_name')) {
                $table->string('bank_name', 100)->nullable()->after('emergency_contact');
            }

            if (!Schema::hasColumn('students', 'bank_account_holder')) {
                $table->string('bank_account_holder', 150)->nullable()->after('bank_name');
            }

            // Stored as TEXT not string because Laravel's `encrypted` cast
            // produces a ciphertext blob much longer than the source bank
            // account number. A 12-character account becomes ~200 chars
            // after AES + base64 + signature.
            if (!Schema::hasColumn('students', 'bank_account_number')) {
                $table->text('bank_account_number')->nullable()->after('bank_account_holder');
            }

            // Public path under storage/app/public/qrcodes/<filename>.
            // Only the path is stored; the file lives on disk.
            if (!Schema::hasColumn('students', 'qr_code_path')) {
                $table->string('qr_code_path', 255)->nullable()->after('bank_account_number');
            }
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            foreach (['bank_name', 'bank_account_holder', 'bank_account_number', 'qr_code_path'] as $c) {
                if (Schema::hasColumn('students', $c)) {
                    $table->dropColumn($c);
                }
            }
        });
    }
};
