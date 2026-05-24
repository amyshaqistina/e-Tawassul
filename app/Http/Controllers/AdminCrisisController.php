<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Crisis;
use App\Models\CrisisReport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
 *
 * Donation control endpoints (added 2026-05-24):
 *   POST /admin/crisis/{crisis}/donation-cap
 *   POST /admin/crisis/{crisis}/toggle-donation
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

    // ==================================================================
    // DONATION CONTROL — admin-only endpoints for managing the public
    // donation page per crisis case.
    // ==================================================================

    /**
     * Update the donation cap and auto-close preference for a crisis.
     *
     * Route: POST /admin/crisis/{crisis}/donation-cap
     */
    public function updateDonationCap(Request $request, Crisis $crisis)
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin->active) abort(403);

        $validated = $request->validate([
            'donation_target'      => ['required', 'numeric', 'min:100', 'max:1000000'],
            'auto_close_on_target' => ['nullable', 'boolean'],
        ], [
            'donation_target.min' => 'Donation cap must be at least RM 100.',
            'donation_target.max' => 'Donation cap cannot exceed RM 1,000,000.',
        ]);

        $crisis->update([
            'donation_target'      => $validated['donation_target'],
            'auto_close_on_target' => (bool) ($validated['auto_close_on_target'] ?? false),
        ]);

        // If auto-close is on AND the cap is already met by previous
        // donations, close immediately so the page reflects reality.
        if ($crisis->auto_close_on_target
            && $crisis->donation_raised >= $crisis->donation_target
            && $crisis->donation_open) {
            $crisis->update([
                'donation_open'          => false,
                'donation_closed_at'     => now(),
                'donation_closed_reason' => 'Goal reached (auto-close on cap update)',
            ]);
        }

        ActivityLog::record(
            'admin',
            (string) $admin->admin_id,
            'donation_cap_updated',
            "Set donation cap to RM {$crisis->donation_target} for crisis #{$crisis->crisis_id}"
        );

        return back()->with('status', 'Donation cap updated.');
    }

    /**
     * Toggle whether the public donate page accepts donations for this
     * crisis. Same endpoint handles open->closed and closed->open; the
     * action is inferred from current state.
     *
     * Route: POST /admin/crisis/{crisis}/toggle-donation
     */
    public function toggleDonation(Request $request, Crisis $crisis)
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin->active) abort(403);

        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:200'],
        ]);

        $wasOpen = (bool) $crisis->donation_open;

        if ($wasOpen) {
            // CLOSING — record when and why
            $crisis->update([
                'donation_open'          => false,
                'donation_closed_at'     => now(),
                'donation_closed_reason' => $validated['reason'] ?? 'Manually closed by admin',
            ]);
            $action  = 'donation_closed';
            $message = "Closed donation for crisis #{$crisis->crisis_id}";
            $flash   = 'Donation page closed. The public donate form is now hidden.';
        } else {
            // RE-OPENING — clear the closed-at timestamp + reason
            $crisis->update([
                'donation_open'          => true,
                'donation_closed_at'     => null,
                'donation_closed_reason' => null,
            ]);
            $action  = 'donation_reopened';
            $message = "Re-opened donation for crisis #{$crisis->crisis_id}";
            $flash   = 'Donation page re-opened. Donors can now contribute again.';
        }

        ActivityLog::record('admin', (string) $admin->admin_id, $action, $message);

        return back()->with('status', $flash);
    }
}
