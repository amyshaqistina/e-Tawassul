<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Crisis;
use App\Models\CrisisReport;
use App\Models\DeathConfirmation;
use App\Models\Donation;
use App\Models\Ldms;
use App\Models\Student;

/**
 * AdminController
 *
 * Handles admin overview / cross-cutting pages that aren't tied to one
 * specific feature workflow:
 *  - dashboard  : landing page with stat cards and recent activity
 *  - students   : full list of students
 *  - donations  : full list of donations
 *
 * Feature-specific admin pages live in their own controllers:
 *  - AdminCrisisController     -> admin crisis report list
 *  - CrisisReportController    -> admin view/verify/reject a single report
 *  - DeathConfirmationController -> admin death confirmation list & actions
 *  - BlockchainController      -> blockchain audit pages
 */
class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'pending_reports'    => CrisisReport::where('report_status', 'pending')->count(),
            'verified_reports'   => CrisisReport::where('report_status', 'verified')->count(),
            'active_crises'      => Crisis::where('status', 'active')->count(),
            'pending_deaths'     => DeathConfirmation::where('status', 'pending')->count(),
            'verified_deaths'    => DeathConfirmation::where('status', 'verified')->count(),
            'total_students'     => Student::count(),
            'active_students'    => Student::where('status', 'active')->count(),
            'total_donations'    => (float) Donation::sum('donation_amount'),
            'donations_count'    => Donation::count(),
            'released_ldms'      => Ldms::where('is_released', true)->count(),
        ];

        $recentActivity = ActivityLog::orderByDesc('timestamp')->limit(15)->get();

        $recentCrises = Crisis::with('student')
            ->orderByDesc('date_reported')
            ->limit(6)
            ->get();

        $pendingReports = CrisisReport::with(['student', 'crisis'])
            ->where('report_status', 'pending')
            ->orderByDesc('date_reported')
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact(
            'stats', 'recentActivity', 'recentCrises', 'pendingReports'
        ));
    }

    public function students()
    {
        $students = Student::orderByDesc('imaalum_synced_at')->paginate(25);
        return view('admin.students.index', compact('students'));
    }

    public function donations()
    {
        $donations = Donation::with('crisis')
            ->orderByDesc('donation_date')
            ->paginate(25);

        $totalRaised = (float) Donation::sum('donation_amount');

        return view('admin.donations.index', compact('donations', 'totalRaised'));
    }
}
