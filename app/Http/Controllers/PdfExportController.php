<?php

namespace App\Http\Controllers;

use App\Models\Crisis;
use App\Models\Donation;
use Barryvdh\DomPDF\Facade\Pdf;

class PdfExportController extends Controller
{
    public function crisisReceipt(Crisis $crisis)
    {
        $crisis->load('student', 'reports');
        $donorCount = Donation::where('crisis_id', $crisis->crisis_id)
            ->distinct('donor_email')->count('donor_email');

        $pdf = Pdf::loadView('pdf.crisis-receipt', [
            'crisis'      => $crisis,
            'donorCount'  => $donorCount,
            'generatedAt' => now(),
        ])->setPaper('a4', 'portrait');

        $filename = 'e-tawassul-crisis-' . $crisis->crisis_id . '.pdf';
        return $pdf->download($filename);
    }

    public function donationReceipt(Donation $donation)
    {
        $donation->load('crisis');

        $pdf = Pdf::loadView('pdf.donation-receipt', [
            'donation'    => $donation,
            'generatedAt' => now(),
        ])->setPaper('a4', 'portrait');

        $filename = 'e-tawassul-donation-' . $donation->donation_id . '.pdf';
        return $pdf->download($filename);
    }
}
