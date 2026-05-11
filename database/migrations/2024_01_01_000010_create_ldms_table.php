<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ldms', function (Blueprint $table) {
            $table->id('ldms_id');
            $table->unsignedBigInteger('confirmation_id')->nullable();
            $table->unsignedBigInteger('nok_id')->nullable();
            $table->unsignedBigInteger('crisis_id')->nullable();
            $table->string('student_id', 20);
            $table->timestamp('date_triggered')->nullable();
            $table->boolean('triggered_by_kin')->default(false);
            $table->boolean('is_released')->default(false);
            $table->longText('message_content')->nullable();
            $table->enum('media_type', ['text', 'image', 'audio', 'mixed'])->default('text');
            $table->json('media_file_path')->nullable();
            $table->timestamps();

            $table->foreign('confirmation_id')->references('confirmation_id')->on('death_confirmation')->nullOnDelete();
            $table->foreign('nok_id')->references('nok_id')->on('next_of_kin')->nullOnDelete();
            $table->foreign('crisis_id')->references('crisis_id')->on('crisis')->nullOnDelete();
            $table->foreign('student_id')->references('student_id')->on('students')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ldms');
    }
};
