<?php

namespace App\Http\Controllers;

use App\Models\CrisisReport;
use App\Models\DeathConfirmation;
use App\Models\Ldms;
use App\Models\NotificationLog;
use Illuminate\Support\Facades\Auth;

class NOKController extends Controller
{
    public function dashboard()
    {
        /** @var \App\Models\NextOfKin $nok */
        $nok = Auth::guard('nok')->user();
        $nok->load('student');

        $student = $nok->student;

        $releasedLdms = collect();
        if ($student) {
            $releasedLdms = Ldms::where('student_id', $student->student_id)
                ->where('is_released', true)
                ->orderByDesc('date_triggered')
                ->get();
        }

        $myConfirmations = DeathConfirmation::where('nok_id', $nok->nok_id)
            ->orderByDesc('date_triggered')
            ->get();

        // Crisis reports this NOK submitted on behalf of the student.
        $myCrisisReports = CrisisReport::with('crisis')
            ->where('nok_id', $nok->nok_id)
            ->where('submitted_by_nok', true)
            ->orderByDesc('date_reported')
            ->take(5)
            ->get();

        $latestCrisisReport = $myCrisisReports->first();

        $notifications = NotificationLog::forRecipient('nok', (string) $nok->nok_id)
            ->orderByDesc('timestamp')
            ->take(10)
            ->get();

        $unreadCount = NotificationLog::forRecipient('nok', (string) $nok->nok_id)
            ->unread()->count();

        return view('nok.dashboard', [
            'nok'                => $nok,
            'student'            => $student,
            'releasedLdms'       => $releasedLdms,
            'myConfirmations'    => $myConfirmations,
            'myCrisisReports'    => $myCrisisReports,
            'latestCrisisReport' => $latestCrisisReport,
            'notifications'      => $notifications,
            'unreadCount'        => $unreadCount,
        ]);
    }
}
