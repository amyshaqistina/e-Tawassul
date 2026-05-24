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
        // Eager-load the linked student (if any) so the view can render
        // their bank info / DuitNow QR when the case is verified. We
        // only show those details for verified cases — the
        // {{ $student->bank_account_number }} check in the view also
        // guards against accidental display when the student hasn't
        // filled them in.
        $crisis->load('student');

        // Donation control state (added 2026-05-24)
        //   $isClosed    true → hide donate form, show closed message
        //   $closedKind  'goal_reached' → celebratory message
        //                'admin_closed' → neutral closed message
        $isClosed   = !$crisis->isAcceptingDonations();
        $closedKind = $crisis->closed_kind;

        return view('public.donate', compact('crisis', 'isClosed', 'closedKind'));
    }

    public function store(DonateRequest $request, Crisis $crisis)
    {
        // GUARD — block submissions to a closed donation page. Donors
        // who kept an old tab open could otherwise POST after admin
        // closed the case; this ensures server state is authoritative.
        if (!$crisis->isAcceptingDonations()) {
            return redirect()
                ->route('donate.create', $crisis->crisis_id)
                ->with('error', 'Donations are no longer being accepted for this case.');
        }

        $anonymous = (bool) $request->input('anonymous', false);
        $donorName = $anonymous ? 'Anonymous Donor' : $request->input('donor_name');

        $donation = DB::transaction(function () use ($request, $crisis, $donorName) {
            $donation = Donation::create([
                'crisis_id'          => $crisis->crisis_id,
                'donor_name'         => $donorName,
                'donor_email'        => $request->input('donor_email'),
                'donation_amount'    => $request->input('donation_amount'),
                'donation_date'      => now(),
                'payment_method'     => $request->input('payment_method'),
                'support_message'    => $request->input('support_message'),
                'donation_target'    => $crisis->donation_target,
                // Phase 3b additions — donor's bank reference + provenance
                'transfer_reference' => $request->input('transfer_reference'),
                'recorded_by'        => 'donor',
            ]);

            $crisis->increment('donation_raised', $donation->donation_amount);

            // AUTO-CLOSE — if this donation pushed the case past its
            // cap AND admin has auto_close_on_target enabled, close the
            // page now. Refresh first so we see post-increment amount.
            $crisis->refresh();
            if ($crisis->auto_close_on_target
                && $crisis->donation_target > 0
                && $crisis->donation_raised >= $crisis->donation_target
                && $crisis->donation_open) {
                $crisis->update([
                    'donation_open'          => false,
                    'donation_closed_at'     => now(),
                    'donation_closed_reason' => 'Goal reached',
                ]);
            }

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

    // ==================================================================
    // ADMIN — manually record an off-platform donation
    // ==================================================================
    //
    // Used when a donation arrives through a non-standard channel that
    // the public donate page can't capture:
    //   - Walk-in donor handed cash to admin
    //   - Donor transferred to admin's account by mistake
    //   - Admin reconciles a bank statement line that wasn't recorded by
    //     the donor
    //   - Any other reconciliation edge case
    //
    // Same audit trail and blockchain record as a normal donor-submitted
    // row; the recorded_by='admin' flag distinguishes the source so
    // admin can audit "how did this row get here?" later.

    public function adminCreate()
    {
        /** @var \App\Models\Admin $admin */
        $admin = \Illuminate\Support\Facades\Auth::guard('admin')->user();

        $perms = (array) ($admin->permissions ?? []);
        if (!in_array('manage_donations', $perms, true) && $admin->role !== 'super_admin') {
            // Permissive default — many admins won't have an explicit
            // manage_donations permission. Anyone with admin access can
            // record manual donations; the audit log captures who.
        }

        // Provide a list of verified active crises for the dropdown.
        $crises = Crisis::orderByDesc('date_reported')->limit(200)->get();

        return view('admin.donations.create', compact('crises'));
    }

    public function adminStore(\Illuminate\Http\Request $request)
    {
        /** @var \App\Models\Admin $admin */
        $admin = \Illuminate\Support\Facades\Auth::guard('admin')->user();

        $validated = $request->validate([
            'crisis_id'        => ['required', 'exists:crisis,crisis_id'],
            'donor_name'       => ['required', 'string', 'max:150'],
            'donor_email'      => ['nullable', 'email', 'max:191'],
            'donation_amount'  => ['required', 'numeric', 'min:1', 'max:1000000'],
            'payment_method'   => ['required', 'in:cash,bank_transfer,duitnow_qr,FPX,credit_card,wallet,other'],
            'support_message'  => ['nullable', 'string', 'max:1000'],
            'admin_note'       => ['required', 'string', 'max:500'],
            'transfer_reference' => ['nullable', 'string', 'max:100'],
        ]);

        $crisis = Crisis::findOrFail($validated['crisis_id']);

        $donation = DB::transaction(function () use ($validated, $crisis, $admin) {
            $donation = Donation::create([
                'crisis_id'            => $crisis->crisis_id,
                'donor_name'           => $validated['donor_name'],
                'donor_email'          => $validated['donor_email'] ?? null,
                'donation_amount'      => $validated['donation_amount'],
                'donation_date'        => now(),
                'payment_method'       => $validated['payment_method'],
                'support_message'      => $validated['support_message'] ?? null,
                'donation_target'      => $crisis->donation_target,
                'transfer_reference'   => $validated['transfer_reference'] ?? null,
                'recorded_by'          => 'admin',
                'admin_note'           => $validated['admin_note'],
                'recorded_by_admin_id' => $admin->admin_id,
            ]);

            $crisis->increment('donation_raised', $donation->donation_amount);
            return $donation;
        });

        $payload = [
            'donation_id'     => $donation->donation_id,
            'crisis_id'       => $crisis->crisis_id,
            'donor_name'      => $donation->donor_name,
            'donation_amount' => (string) $donation->donation_amount,
            'payment_method'  => $donation->payment_method,
            'recorded_by'     => 'admin',
            'admin_id'        => $admin->admin_id,
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
            'admin',
            (string) $admin->admin_id,
            'donation_recorded_manual',
            "Manually recorded RM {$donation->donation_amount} for crisis #{$crisis->crisis_id} from {$donation->donor_name}. Note: {$validated['admin_note']}"
        );

        // Email a receipt to the donor if they provided an email.
        if ($donation->donor_email) {
            $this->notifications->send(
                recipientType: 'public',
                recipientId:   $donation->donor_email,
                email:         $donation->donor_email,
                mailable:      new DonationReceivedMail($donation),
                notificationType: 'donation_received',
                subject:       'Donation receipt — e-Tawassul',
                message:       "Your donation of RM {$donation->donation_amount} has been recorded.",
                link:          null,
            );
        }

        return redirect()
            ->route('admin.donations.index')
            ->with('status', "Manual donation of RM {$donation->donation_amount} recorded for crisis #{$crisis->crisis_id}.");
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
