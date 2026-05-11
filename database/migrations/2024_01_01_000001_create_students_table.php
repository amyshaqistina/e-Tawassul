<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->string('student_id', 20)->primary();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('email')->unique();
            $table->string('kulliyyah', 150)->nullable();
            $table->string('programme', 150)->nullable();
            $table->string('year_of_study', 10)->nullable();
            $table->string('mahallah', 100)->nullable();
            $table->string('phone', 20)->nullable();
            $table->enum('gender', ['Male', 'Female'])->nullable();
            $table->string('nationality', 50)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('enrollment_status', 50)->default('Active');
            $table->string('emergency_contact', 20)->nullable();
            $table->timestamp('imaalum_synced_at')->nullable();
            $table->enum('status', ['active', 'deceased'])->default('active');
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
