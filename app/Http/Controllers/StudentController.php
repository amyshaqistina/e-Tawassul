<?php

namespace App\Http\Controllers;

use App\Jobs\ScrapeImaalumData;
use App\Models\CrisisReport;
use App\Models\Ldms;
use App\Models\NotificationLog;
use Illuminate\Support\Facades\Auth;

class StudentController extends Controller
{
    public function dashboard()
    {
        /** @var \App\Models\Student $student */
        $student = Auth::guard('student')->user();

        $scrapeStale = !$student->imaalum_synced_at
            || $student->imaalum_synced_at->lt(now()->subDay());

        if ($scrapeStale) {
            try {
                ScrapeImaalumData::dispatch($student->student_id, null);
            } catch (\Throwable $e) {
                // never block dashboard
            }
        }

        $reports = CrisisReport::where('student_id', $student->student_id)
            ->orderByDesc('date_reported')
            ->take(5)
            ->get();

        $ldmsCount = Ldms::where('student_id', $student->student_id)->count();

        $notifications = NotificationLog::forRecipient('student', $student->student_id)
            ->orderByDesc('timestamp')
            ->take(5)
            ->get();

        $unreadCount = NotificationLog::forRecipient('student', $student->student_id)
            ->unread()->count();

        return view('student.dashboard', [
            'student'       => $student,
            'reports'       => $reports,
            'ldmsCount'     => $ldmsCount,
            'notifications' => $notifications,
            'unreadCount'   => $unreadCount,
            'scrapeStale'   => $scrapeStale,
        ]);
    }

    public function profile()
    {
        /** @var \App\Models\Student $student */
        $student = Auth::guard('student')->user();
        $student->load('nextOfKin');

        return view('student.profile', ['student' => $student]);
    }
}
