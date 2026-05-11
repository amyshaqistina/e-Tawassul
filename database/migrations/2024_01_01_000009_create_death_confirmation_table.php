<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('death_confirmation', function (Blueprint $table) {
            $table->id('confirmation_id');
            $table->unsignedBigInteger('crisis_id')->nullable();
            $table->unsignedBigInteger('nok_id');
            $table->string('student_id', 20);
            $table->timestamp('date_triggered')->useCurrent();
            $table->timestamp('date_confirmed')->nullable();
            $table->boolean('verified_by_kin')->default(false);
            $table->timestamp('verified_by_kin_date')->nullable();
            $table->string('media_file_path', 500)->nullable();
            $table->string('media_file_name', 255)->nullable();
            $table->unsignedBigInteger('media_file_size')->nullable();
            $table->text('admin_comments')->nullable();
            $table->enum('status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->string('blockchain_reference', 128)->nullable();
            $table->timestamps();

            $table->foreign('crisis_id')->references('crisis_id')->on('crisis')->nullOnDelete();
            $table->foreign('nok_id')->references('nok_id')->on('next_of_kin')->cascadeOnDelete();
            $table->foreign('student_id')->references('student_id')->on('students')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('death_confirmation');
    }
};
