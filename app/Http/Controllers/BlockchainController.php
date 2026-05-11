<?php

namespace App\Http\Controllers;

use App\Http\Requests\VerifyHashRequest;
use App\Models\Blockchain;
use App\Services\BlockchainService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class BlockchainController extends Controller
{
    public function __construct(protected BlockchainService $blockchain) {}

    public function index()
    {
        $records = Blockchain::orderByDesc('timestamp')->paginate(25);

        $stats = [
            'total'      => Blockchain::count(),
            'quorum'     => Blockchain::where('mode', 'quorum')->count(),
            'simulation' => Blockchain::where('mode', 'simulation')->count(),
            'by_type'    => Blockchain::selectRaw('data_from, COUNT(*) as n')
                                ->groupBy('data_from')->pluck('n', 'data_from')->toArray(),
        ];

        return view('admin.blockchain.index', compact('records', 'stats'));
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
}
