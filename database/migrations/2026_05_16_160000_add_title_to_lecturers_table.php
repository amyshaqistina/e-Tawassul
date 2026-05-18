<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lecturers', function (Blueprint $table) {
            // e.g. "Prof. Dr.", "Assoc. Prof. Ts. Dr.", "Dr." — used as salutation in emails
            $table->string('title', 100)->nullable()->after('last_name');
        });
    }

    public function down(): void
    {
        Schema::table('lecturers', function (Blueprint $table) {
            $table->dropColumn('title');
        });
    }
};
