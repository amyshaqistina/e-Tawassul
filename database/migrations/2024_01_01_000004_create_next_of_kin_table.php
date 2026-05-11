<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('next_of_kin', function (Blueprint $table) {
            $table->id('nok_id');
            $table->string('student_id', 20);
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('relationship_to_student', 50);
            $table->string('email')->unique();
            $table->string('phone', 20);
            $table->enum('access_level', ['standard', 'full'])->default('standard');
            $table->boolean('emergency_contact_verified')->default(false);
            $table->timestamp('consent_date')->nullable();
            $table->timestamp('expiry_date')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();

            $table->foreign('student_id')->references('student_id')->on('students')
                ->cascadeOnUpdate()->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('next_of_kin');
    }
};
