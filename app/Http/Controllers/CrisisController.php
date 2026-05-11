<?php

namespace App\Http\Controllers;

use App\Models\Crisis;
use App\Models\Donation;

class CrisisController extends Controller
{
    public function index()
    {
        $activeCrises = Crisis::with('student')
            ->whereIn('status', ['active', 'pending'])
            ->orderByDesc('date_reported')
            ->get();

        $stats = [
            'total_active'     => Crisis::where('status', 'active')->count(),
            'total_resolved'   => Crisis::where('status', 'resolved')->count(),
            'total_raised'     => (float) Donation::sum('donation_amount'),
            'total_supporters' => Donation::distinct('donor_email')->count('donor_email'),
        ];

        return view('public.dashboard', compact('activeCrises', 'stats'));
    }

    public function show(Crisis $crisis)
    {
        $crisis->load('student', 'reports');

        $recentDonations = Donation::where('crisis_id', $crisis->crisis_id)
            ->orderByDesc('donation_date')
            ->take(15)
            ->get();

        $donorCount = Donation::where('crisis_id', $crisis->crisis_id)
            ->distinct('donor_email')
            ->count('donor_email');

        return view('public.crisis-show', compact('crisis', 'recentDonations', 'donorCount'));
    }
}
