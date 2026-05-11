<?php

namespace App\Http\Controllers;

use App\Http\Requests\DonateRequest;
use App\Mail\DonationReceivedMail;
use App\Models\ActivityLog;
use App\Models\Crisis;
use App\Models\Donation;
use App\Services\BlockchainService;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DonationController extends Controller
{
    public function __construct(
        protected BlockchainService $blockchain,
        protected NotificationService $notifications,
    ) {}

    public function create(Crisis $crisis)
    {
        $crisis->load('student');
        return view('public.donate', compact('crisis'));
    }

    public function store(DonateRequest $request, Crisis $crisis)
    {
        $anonymous = (bool) $request->input('anonymous', false);
        $donorName = $anonymous ? 'Anonymous Donor' : $request->input('donor_name');

        $donation = DB::transaction(function () use ($request, $crisis, $donorName) {
            $donation = Donation::create([
                'crisis_id'       => $crisis->crisis_id,
                'donor_name'      => $donorName,
                'donor_email'     => $request->input('donor_email'),
                'donation_amount' => $request->input('donation_amount'),
                'donation_date'   => now(),
                'payment_method'  => $request->input('payment_method'),
                'support_message' => $request->input('support_message'),
                'donation_target' => $crisis->donation_target,
            ]);

            $crisis->increment('donation_raised', $donation->donation_amount);
            return $donation;
        });

        $payload = [
            'donation_id'     => $donation->donation_id,
            'crisis_id'       => $crisis->crisis_id,
            'donor_email'     => $donation->donor_email,
            'donation_amount' => (string) $donation->donation_amount,
            'payment_method'  => $donation->payment_method,
            'donation_date'   => $donation->donation_date->toIso8601String(),
        ];
        $result = $this->blockchain->recordEvent(
            'DONATION_RECORDED',
            $payload,
            $donation->donation_id,
            'donation'
        );
        $donation->update(['blockchain_hash' => $result['hash']]);

        ActivityLog::record(
            'public',
            $donation->donor_email,
            'donation_recorded',
            "Donation of RM {$donation->donation_amount} for crisis #{$crisis->crisis_id}"
        );

        $this->notifications->send(
            recipientType: 'public',
            recipientId:   $donation->donor_email,
            email:         $donation->donor_email,
            mailable:      new DonationReceivedMail($donation),
            notificationType: 'donation_received',
            subject:       'Thank you for your donation',
            message:       "Your donation of RM {$donation->donation_amount} has been received.",
            link:          null,
        );

        return redirect()
            ->route('crisis.show', $crisis->crisis_id)
            ->with('status', 'Thank you! Your donation has been recorded.')
            ->with('donation_id', $donation->donation_id);
    }

    public function progress(Crisis $crisis): JsonResponse
    {
        $crisis->refresh();
        return response()->json([
            'crisis_id'  => $crisis->crisis_id,
            'raised'     => (float) $crisis->donation_raised,
            'target'     => (float) $crisis->donation_target,
            'percent'    => $crisis->progress_percent,
            'updated_at' => now()->toIso8601String(),
        ]);
    }
}
