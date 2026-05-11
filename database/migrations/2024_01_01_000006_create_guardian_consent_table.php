<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guardian_consent', function (Blueprint $table) {
            $table->id('consent_id');
            $table->string('student_id', 20);
            $table->unsignedBigInteger('guardian_id');
            $table->boolean('access_granted')->default(false);
            $table->boolean('emergency_contact_verified')->default(false);
            $table->timestamp('consent_date')->nullable();
            $table->timestamp('expiry_date')->nullable();
            $table->timestamps();

            $table->foreign('student_id')->references('student_id')->on('students')->cascadeOnDelete();
            $table->foreign('guardian_id')->references('nok_id')->on('next_of_kin')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guardian_consent');
    }
};
