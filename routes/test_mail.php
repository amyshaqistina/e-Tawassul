<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;

Route::get('/test-lecturer-mail', function () {
    $student = DB::table('student_courses')->first();
    if (!$student) return "No student courses found in DB!";

    $courses = DB::table('student_courses')
        ->where('student_id', $student->student_id)
        ->join('lecturers', 'student_courses.lecturer_id', '=', 'lecturers.lecturer_id')
        ->get();

    foreach ($courses as $course) {
        Mail::raw('SISTEM TEST: Notifikasi Lecturer Berfungsi!', function ($m) use ($course) {
            $m->to('nabilahnordin20082002@gmail.com')
              ->subject('TEST: Notify Lecturer ' . ($course->course_code ?? 'Unknown'));
        });
    }

    return "SUCCESS: Triggered " . $courses->count() . " emails to Mailtrap!";
});
