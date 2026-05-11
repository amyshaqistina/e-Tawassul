<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crisis_report', function (Blueprint $table) {
            $table->id('report_id');
            $table->string('student_id', 20);
            $table->unsignedBigInteger('crisis_id');
            $table->text('report_description');
            $table->enum('report_status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->timestamp('date_reported')->useCurrent();
            $table->unsignedBigInteger('admin_verification')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->text('admin_remarks')->nullable();
            $table->string('blockchain_hash', 128)->nullable();
            $table->json('supporting_evidence_path')->nullable();
            $table->timestamps();

            $table->foreign('student_id')->references('student_id')->on('students')->cascadeOnDelete();
            $table->foreign('crisis_id')->references('crisis_id')->on('crisis')->cascadeOnDelete();
            $table->foreign('admin_verification')->references('admin_id')->on('admins')->nullOnDelete();
            $table->index('report_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crisis_report');
    }
};
