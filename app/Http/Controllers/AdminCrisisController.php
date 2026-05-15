<?php

namespace App\Http\Controllers;

use App\Models\CrisisReport;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Admin-side crisis report listing.
 *
 * GET /admin/crisis (admin.crisis.index)
 *
 * NOTE on the search field:
 *   The student `full_name` is an Eloquent accessor (computed from first/last name),
 *   not a real DB column — so it cannot appear in a SQL WHERE clause. We search on
 *   what IS in the students table: student_id (matric) and the real name columns
 *   if you have them. Adjust the column list in $studentNameColumns below to match
 *   your actual `students` schema.
 */
class AdminCrisisController extends Controller
{
    /**
     * Real column names in the `students` table that should be searched
     * for free-text queries. Add/remove to match your schema.
     */
    protected array $studentNameColumns = [
        'student_id',    // matric number
        // 'first_name', // uncomment if these columns exist
        // 'last_name',
        // 'name',
    ];

    public function index(Request $request)
    {
        $tab = in_array($request->query('tab'), ['pending', 'verified', 'rejected'], true)
            ? $request->query('tab')
            : 'pending';

        $applyFilters = function ($query) use ($request) {
            // Free-text search — only on REAL DB columns (not accessors like full_name)
            if ($s = trim((string) $request->query('search'))) {
                $query->where(function ($w) use ($s) {
                    $w->where('crisis_report.report_id', 'like', "%{$s}%")
                      ->orWhere('crisis_report.student_id', 'like', "%{$s}%");

                    // Search related student columns that actually exist
                    $w->orWhereHas('student', function ($sq) use ($s) {
                        $sq->where(function ($inner) use ($s) {
                            foreach ($this->studentNameColumns as $col) {
                                $inner->orWhere($col, 'like', "%{$s}%");
                            }
                        });
                    });
                });
            }

            if ($type = $request->query('crisis_type')) {
                $query->whereHas('crisis', fn ($c) => $c->where('crisis_type', $type));
            }

            if ($sub = $request->query('sub_category')) {
                $query->whereHas('crisis', fn ($c) => $c->where('sub_category', $sub));
            }

            [$from, $to] = $this->resolveDateRange($request);
            if ($from) $query->whereDate('crisis_report.date_reported', '>=', $from);
            if ($to)   $query->whereDate('crisis_report.date_reported', '<=', $to);

            return $query;
        };

        $pending = $applyFilters(
            CrisisReport::with(['student', 'crisis'])
                ->where('report_status', 'pending')
                ->orderByDesc('date_reported')
        )->paginate(15, ['*'], 'pending')->withQueryString();

        $verified = $applyFilters(
            CrisisReport::with(['student', 'crisis'])
                ->where('report_status', 'verified')
                ->orderByDesc('verified_at')
        )->paginate(15, ['*'], 'verified')->withQueryString();

        $rejected = $applyFilters(
            CrisisReport::with(['student', 'crisis'])
                ->where('report_status', 'rejected')
                ->orderByDesc('updated_at')
        )->paginate(15, ['*'], 'rejected')->withQueryString();

        // Category totals (scoped to the active tab + filters except crisis_type)
        $totalsQuery = CrisisReport::query()
            ->from('crisis_report')
            ->where('report_status', $tab);

        if ($s = trim((string) $request->query('search'))) {
            $totalsQuery->where(function ($w) use ($s) {
                $w->where('crisis_report.report_id', 'like', "%{$s}%")
                  ->orWhere('crisis_report.student_id', 'like', "%{$s}%");
                $w->orWhereHas('student', function ($sq) use ($s) {
                    $sq->where(function ($inner) use ($s) {
                        foreach ($this->studentNameColumns as $col) {
                            $inner->orWhere($col, 'like', "%{$s}%");
                        }
                    });
                });
            });
        }
        if ($sub = $request->query('sub_category')) {
            $totalsQuery->whereHas('crisis', fn ($c) => $c->where('sub_category', $sub));
        }
        [$from, $to] = $this->resolveDateRange($request);
        if ($from) $totalsQuery->whereDate('crisis_report.date_reported', '>=', $from);
        if ($to)   $totalsQuery->whereDate('crisis_report.date_reported', '<=', $to);

        $categoryTotals = $totalsQuery
            ->join('crisis', 'crisis_report.crisis_id', '=', 'crisis.crisis_id')
            ->selectRaw('crisis.crisis_type, COUNT(*) as total')
            ->groupBy('crisis.crisis_type')
            ->pluck('total', 'crisis.crisis_type')
            ->toArray();

        return view('admin.crisis.index', compact(
            'tab', 'pending', 'verified', 'rejected', 'categoryTotals'
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
