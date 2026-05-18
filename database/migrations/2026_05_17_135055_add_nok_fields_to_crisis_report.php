<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crisis_report', function (Blueprint $table) {
            $table->unsignedBigInteger('nok_id')->nullable()->after('student_id');
            $table->boolean('submitted_by_nok')->default(false)->after('nok_id');

            $table->foreign('nok_id')
                ->references('nok_id')
                ->on('next_of_kin')
                ->nullOnDelete();

            $table->index('submitted_by_nok');
        });
    }

    public function down(): void
    {
        Schema::table('crisis_report', function (Blueprint $table) {
            $table->dropForeign(['nok_id']);
            $table->dropIndex(['submitted_by_nok']);
            $table->dropColumn(['nok_id', 'submitted_by_nok']);
        });
    }
};
