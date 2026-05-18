<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_courses', function (Blueprint $table) {
            $table->id();

            $table->string('student_id', 20);
            $table->unsignedBigInteger('lecturer_id');

            $table->string('course_code', 20);
            $table->string('course_name')->nullable();
            $table->string('section', 10)->nullable();
            $table->string('semester', 30)->nullable();

            $table->string('lecturer_name_raw')->nullable();

            $table->timestamps();

            $table->foreign('student_id')
                ->references('student_id')->on('students')
                ->onDelete('cascade');

            $table->foreign('lecturer_id')
                ->references('lecturer_id')->on('lecturers')
                ->onDelete('cascade');

            $table->unique(['student_id', 'course_code', 'semester'], 'student_course_unique');

            $table->index('student_id');
            $table->index('lecturer_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_courses');
    }
};
