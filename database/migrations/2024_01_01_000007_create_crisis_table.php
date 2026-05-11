<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crisis', function (Blueprint $table) {
            $table->id('crisis_id');
            $table->enum('crisis_type', ['death', 'accident', 'illness', 'natural_disaster', 'family_emergency']);
            $table->text('crisis_description');
            $table->text('crisis_details')->nullable();
            $table->enum('impact_level', ['critical', 'high', 'medium', 'low'])->default('medium');
            $table->string('location', 255)->nullable();
            $table->timestamp('date_reported')->useCurrent();
            $table->enum('status', ['pending', 'active', 'resolved', 'closed'])->default('pending');
            $table->decimal('donation_target', 12, 2)->default(0);
            $table->decimal('donation_raised', 12, 2)->default(0);
            $table->string('student_id', 20)->nullable();
            $table->timestamps();

            $table->foreign('student_id')->references('student_id')->on('students')->nullOnDelete();
            $table->index('status');
            $table->index('impact_level');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crisis');
    }
};
