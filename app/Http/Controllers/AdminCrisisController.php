<?php

namespace App\Http\Controllers;

use App\Models\CrisisReport;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Admin-side crisis report listing.
 *
 * Handles:
 *  - GET /admin/crisis           (admin.crisis.index)
 *
 * The actual review actions (show / verify / reject) still live on
 * CrisisReportController so they share validation, blockchain, and
 * notification logic with the student-side flow.
 *
 * NOTE on table names:
 *  - crisis_report  (singular, no 's')
 *  - crisis         (singular, no 's')
 * Both confirmed against the migrations in database/migrations/.
 */
class AdminCrisisController extends Controller
{
    /**
     * Admin crisis report list — supports:
     *  - tab           (pending|verified|rejected)
     *  - search        (matches report_id, student_id, student full_name)
     *  - crisis_type   (medical|accident|natural_disaster|death|...)
     *  - sub_category  (e.g. road_accident, flood) — only used if the column exists on crisis
     *  - date_range    (today|week|last_week|month|custom)
     *  - date_from + date_to (when date_range=custom)
     *
     * Provides $categoryTotals (crisis_type => count) for the summary chips,
     * scoped to the active tab and respecting all filters except crisis_type.
     */
    public function index(Request $request)
    {
        $tab = in_array($request->query('tab'), ['pending', 'verified', 'rejected'], true)
            ? $request->query('tab')
            : 'pending';

        // Reusable filter applier for the three status paginators
        $applyFilters = function ($query) use ($request) {
            // Free-text search
            if ($s = trim((string) $request->query('search'))) {
                $query->where(function ($w) use ($s) {
                    $w->where('report_id', 'like', "%{$s}%")
                      ->orWhere('student_id', 'like', "%{$s}%")
                      ->orWhereHas('student', function ($sq) use ($s) {
                          $sq->where('full_name', 'like', "%{$s}%");
                      });
                });
            }

            // Crisis type
            if ($type = $request->query('crisis_type')) {
                $query->whereHas('crisis', fn ($c) => $c->where('crisis_type', $type));
            }

            // Sub-category (silently ignored if the column doesn't exist on crisis)
            if ($sub = $request->query('sub_category')) {
                $query->whereHas('crisis', fn ($c) => $c->where('sub_category', $sub));
            }

            // Date range
            [$from, $to] = $this->resolveDateRange($request);
            if ($from) $query->whereDate('date_reported', '>=', $from);
            if ($to)   $query->whereDate('date_reported', '<=', $to);

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

        // Category totals across the active tab (ignores crisis_type filter so chips
        // show all categories present in the filtered result set).
        $totalsQuery = CrisisReport::query()
            ->from('crisis_report')                // explicit singular table name
            ->where('report_status', $tab);

        if ($s = trim((string) $request->query('search'))) {
            $totalsQuery->where(function ($w) use ($s) {
                $w->where('crisis_report.report_id', 'like', "%{$s}%")
                  ->orWhere('crisis_report.student_id', 'like', "%{$s}%")
                  ->orWhereHas('student', fn ($sq) => $sq->where('full_name', 'like', "%{$s}%"));
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

    /**
     * Resolve the date_range query into [from, to] Carbon dates (or [null, null]).
     */
    protected function resolveDateRange(Request $request): array
    {
        switch ($request->query('date_range')) {
            case 'today':
                return [Carbon::today(), Carbon::today()->endOfDay()];

            case 'week':
                return [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()];

            case 'last_week':
                return [
                    Carbon::now()->subWeek()->startOfWeek(),
                    Carbon::now()->subWeek()->endOfWeek(),
                ];

            case 'month':
                return [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()];

            case 'custom':
                $from = $request->query('date_from')
                    ? Carbon::parse($request->query('date_from'))->startOfDay()
                    : null;
                $to = $request->query('date_to')
                    ? Carbon::parse($request->query('date_to'))->endOfDay()
                    : null;
                return [$from, $to];

            default:
                return [null, null];
        }
    }
}
