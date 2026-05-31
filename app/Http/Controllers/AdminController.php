<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Crisis;
use App\Models\CrisisReport;
use App\Models\DeathConfirmation;
use App\Models\Donation;
use App\Models\Ldms;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\Request;

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

        $pendingDeaths = DeathConfirmation::with('student')
            ->where('status', 'pending')
            ->orderByDesc('date_triggered')
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact(
            'stats', 'recentActivity', 'recentCrises', 'pendingReports', 'pendingDeaths'
        ));
    }

    public function students(Request $request)
    {
        $query = Student::query();

        // Free-text search: student_id / first / last / email / kulliyyah
        if ($s = trim((string) $request->query('search'))) {
            $query->where(function ($w) use ($s) {
                $w->where('student_id', 'like', "%{$s}%")
                  ->orWhere('first_name', 'like', "%{$s}%")
                  ->orWhere('last_name',  'like', "%{$s}%")
                  ->orWhere('email',      'like', "%{$s}%")
                  ->orWhere('kulliyyah',  'like', "%{$s}%")
                  ->orWhere('programme',  'like', "%{$s}%");
            });
        }

        // Status filter — active/deceased/inactive etc.
        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        // Kulliyyah filter (the column stores the full text label)
        if ($kulliyyah = $request->query('kulliyyah')) {
            $query->where('kulliyyah', 'like', "%{$kulliyyah}%");
        }

        // Year of study
        if ($year = $request->query('year')) {
            $query->where('year_of_study', $year);
        }

        // iMaalum sync filter
        if ($sync = $request->query('sync')) {
            if ($sync === 'synced')    $query->whereNotNull('imaalum_synced_at');
            if ($sync === 'never')     $query->whereNull('imaalum_synced_at');
            if ($sync === 'stale')     $query->where('imaalum_synced_at', '<', now()->subDays(7));
        }

        $students = $query->orderByDesc('imaalum_synced_at')
            ->paginate(20)
            ->withQueryString();

        // Status chip totals — counts independent of the active status filter
        // (so the user can see "Active 47 / Deceased 1" regardless of what
        // they're currently viewing).
        $statusTotals = Student::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        // Distinct kulliyyahs for the dropdown
        $kulliyyahOptions = Student::query()
            ->whereNotNull('kulliyyah')
            ->where('kulliyyah', '!=', '')
            ->distinct()
            ->orderBy('kulliyyah')
            ->pluck('kulliyyah');

        return view('admin.students.index', compact(
            'students', 'statusTotals', 'kulliyyahOptions'
        ));
    }

    public function donations(Request $request)
    {
        $applyFilters = function ($query) use ($request) {
            // Search: donor name/email, donation id, crisis id
            if ($s = trim((string) $request->query('search'))) {
                $query->where(function ($w) use ($s) {
                    $w->where('donation.donation_id', 'like', "%{$s}%")
                      ->orWhere('donation.donor_name', 'like', "%{$s}%")
                      ->orWhere('donation.donor_email', 'like', "%{$s}%")
                      ->orWhere('donation.crisis_id', 'like', "%{$s}%");
                });
            }

            // Crisis type filter (joins through crisis_report -> crisis)
            if ($crisisType = $request->query('crisis_type')) {
                $query->whereHas('crisis', fn($c) => $c->where('crisis_type', $crisisType));
            }

            if ($method = $request->query('payment_method')) {
                $query->where('payment_method', $method);
            }

            // Donation amount band
            if ($band = $request->query('amount_band')) {
                switch ($band) {
                    case 'small':  $query->where('donation_amount', '<', 100); break;
                    case 'medium': $query->whereBetween('donation_amount', [100, 500]); break;
                    case 'large':  $query->whereBetween('donation_amount', [500.01, 5000]); break;
                    case 'major':  $query->where('donation_amount', '>', 5000); break;
                }
            }

            [$from, $to] = $this->resolveDateRange($request);
            if ($from) $query->whereDate('donation_date', '>=', $from);
            if ($to)   $query->whereDate('donation_date', '<=', $to);

            return $query;
        };

        $donations = $applyFilters(
            Donation::with('crisis')->orderByDesc('donation_date')
        )->paginate(20)->withQueryString();

        // Totals across the filtered set (not just the page)
        $filteredQuery = $applyFilters(Donation::query());
        $totalFiltered = (float) $filteredQuery->sum('donation_amount');
        $countFiltered = (int)   $filteredQuery->count();

        // Grand total across all donations (for the headline number)
        $totalRaised = (float) Donation::sum('donation_amount');

        // Category chips: count by crisis_type (NB: real table name is `donation`,
        // not `donations` — Eloquent infers wrong by default for this model).
        $categoryTotals = Donation::query()
            ->join('crisis', 'donation.crisis_id', '=', 'crisis.crisis_id')
            ->selectRaw('crisis.crisis_type, COUNT(*) as total, SUM(donation.donation_amount) as amount')
            ->groupBy('crisis.crisis_type')
            ->get()
            ->keyBy('crisis_type');

        return view('admin.donations.index', compact(
            'donations', 'totalRaised', 'totalFiltered', 'countFiltered', 'categoryTotals'
        ));
    }

    protected function resolveDateRange(Request $request): array
    {
        switch ($request->query('date_range')) {
            case 'today':
                return [Carbon::today(), Carbon::today()->endOfDay()];
            case 'week':
                return [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()];
            case 'last_week':
                return [Carbon::now()->subWeek()->startOfWeek(), Carbon::now()->subWeek()->endOfWeek()];
            case 'month':
                return [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()];
            case 'custom':
                $from = $request->query('date_from') ? Carbon::parse($request->query('date_from'))->startOfDay() : null;
                $to   = $request->query('date_to')   ? Carbon::parse($request->query('date_to'))->endOfDay()   : null;
                return [$from, $to];
            default:
                return [null, null];
        }
    }
}
