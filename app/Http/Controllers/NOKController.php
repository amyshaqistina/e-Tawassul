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

    /**
     * Combined "My Submissions" page — shows all crisis reports +
     * death confirmations this NOK has submitted, with status filters.
     */
    public function mySubmissions(\Illuminate\Http\Request $request)
    {
        /** @var \App\Models\NextOfKin $nok */
        $nok = Auth::guard('nok')->user();

        $filter = $request->query('status', 'all');
        $allowed = ['all', 'pending', 'verified', 'rejected'];
        if (!in_array($filter, $allowed, true)) {
            $filter = 'all';
        }

        $reportsQ = CrisisReport::with('crisis')
            ->where('nok_id', $nok->nok_id)
            ->where('submitted_by_nok', true);

        $deathsQ = DeathConfirmation::where('nok_id', $nok->nok_id);

        if ($filter !== 'all') {
            $reportsQ->where('report_status', $filter);
            $deathsQ->where('status', $filter);
        }

        $reports = $reportsQ->orderByDesc('date_reported')->get();
        $deaths  = $deathsQ->orderByDesc('date_triggered')->get();

        // Counts for filter chips (across both types)
        $counts = [
            'all' => CrisisReport::where('nok_id', $nok->nok_id)->where('submitted_by_nok', true)->count()
                   + DeathConfirmation::where('nok_id', $nok->nok_id)->count(),
            'pending' => CrisisReport::where('nok_id', $nok->nok_id)->where('submitted_by_nok', true)->where('report_status', 'pending')->count()
                       + DeathConfirmation::where('nok_id', $nok->nok_id)->where('status', 'pending')->count(),
            'verified' => CrisisReport::where('nok_id', $nok->nok_id)->where('submitted_by_nok', true)->where('report_status', 'verified')->count()
                        + DeathConfirmation::where('nok_id', $nok->nok_id)->where('status', 'verified')->count(),
            'rejected' => CrisisReport::where('nok_id', $nok->nok_id)->where('submitted_by_nok', true)->where('report_status', 'rejected')->count()
                        + DeathConfirmation::where('nok_id', $nok->nok_id)->where('status', 'rejected')->count(),
        ];

        return view('nok.submissions.index', compact('reports', 'deaths', 'filter', 'counts'));
    }
}
