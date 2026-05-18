<?php

namespace App\Http\Controllers;

use App\Http\Requests\VerifyHashRequest;
use App\Models\Blockchain;
use App\Services\BlockchainService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BlockchainController extends Controller
{
    public function __construct(protected BlockchainService $blockchain) {}

    public function index(Request $request)
    {
        $query = Blockchain::query();

        // Free-text search: hash (full or partial), reference table/id
        if ($s = trim((string) $request->query('search'))) {
            $query->where(function ($w) use ($s) {
                $w->where('stored_data',     'like', "%{$s}%")
                  ->orWhere('reference_table','like', "%{$s}%")
                  ->orWhere('reference_id',   'like', "%{$s}%")
                  ->orWhere('data_from',      'like', "%{$s}%");
            });
        }

        // Event-type filter (data_from column holds the event name like
        // CRISIS_VERIFIED, DEATH_CONFIRMED, LDMS_TRIGGERED, DONATION_RECORDED, etc.)
        if ($eventType = $request->query('event_type')) {
            $query->where('data_from', $eventType);
        }

        // Mode filter — simulation vs quorum
        if ($mode = $request->query('mode')) {
            $query->where('mode', $mode);
        }

        // Reference table filter — crisis_report / death_confirmation / ldms / donation
        if ($refTable = $request->query('ref_table')) {
            $query->where('reference_table', $refTable);
        }

        [$from, $to] = $this->resolveDateRange($request);
        if ($from) $query->whereDate('timestamp', '>=', $from);
        if ($to)   $query->whereDate('timestamp', '<=', $to);

        $records = $query->orderByDesc('timestamp')
            ->paginate(20)
            ->withQueryString();

        // Stats — totals across the whole chain, not just the filtered page
        $stats = [
            'total'      => Blockchain::count(),
            'quorum'     => Blockchain::where('mode', 'quorum')->count(),
            'simulation' => Blockchain::where('mode', 'simulation')->count(),
            'by_type'    => Blockchain::selectRaw('data_from, COUNT(*) as n')
                                ->groupBy('data_from')
                                ->orderByDesc('n')
                                ->pluck('n', 'data_from')
                                ->toArray(),
        ];

        // Distinct values for the dropdowns
        $eventTypeOptions = Blockchain::query()
            ->whereNotNull('data_from')
            ->distinct()
            ->orderBy('data_from')
            ->pluck('data_from');

        $refTableOptions = Blockchain::query()
            ->whereNotNull('reference_table')
            ->distinct()
            ->orderBy('reference_table')
            ->pluck('reference_table');

        return view('admin.blockchain.index', compact(
            'records', 'stats', 'eventTypeOptions', 'refTableOptions'
        ));
    }

    public function verify(VerifyHashRequest $request)
    {
        $hash = strtolower($request->input('hash'));
        $record = $this->blockchain->verifyHashString($hash);

        if (!$record) {
            return back()
                ->withInput()
                ->with('verify_result', ['ok' => false, 'hash' => $hash]);
        }

        return back()
            ->withInput()
            ->with('verify_result', ['ok' => true, 'hash' => $hash, 'record' => $record]);
    }

    public function pdfAuditLog()
    {
        $records = Blockchain::orderByDesc('timestamp')->limit(1000)->get();
        $admin = Auth::guard('admin')->user();

        $pdf = Pdf::loadView('pdf.audit-log', [
            'records'     => $records,
            'generatedAt' => now(),
            'admin'       => $admin,
        ])->setPaper('a4', 'landscape');

        $filename = 'e-tawassul-blockchain-audit-' . now()->format('Ymd-His') . '.pdf';
        return $pdf->download($filename);
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
