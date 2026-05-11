<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubmitDeathConfirmationRequest;
use App\Http\Requests\VerifyDeathRequest;
use App\Mail\DeathConfirmationSubmittedMail;
use App\Mail\DeathVerifiedMail;
use App\Models\ActivityLog;
use App\Models\Admin;
use App\Models\Crisis;
use App\Models\DeathConfirmation;
use App\Models\Student;
use App\Services\BlockchainService;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DeathConfirmationController extends Controller
{
    public function __construct(
        protected BlockchainService $blockchain,
        protected NotificationService $notifications,
    ) {}

    // -----------------------------------------------------------
    // NOK
    // -----------------------------------------------------------

    public function create()
    {
        /** @var \App\Models\NextOfKin $nok */
        $nok = Auth::guard('nok')->user();
        $nok->load('student');

        return view('nok.death.create', ['nok' => $nok, 'student' => $nok->student]);
    }

    public function store(SubmitDeathConfirmationRequest $request)
    {
        /** @var \App\Models\NextOfKin $nok */
        $nok = Auth::guard('nok')->user();

        if ($request->input('student_id') !== $nok->student_id) {
            abort(403, 'You can only submit a death confirmation for your linked student.');
        }

        $file = $request->file('media_file');
        $path = $file->store('death-confirmations', 'local');

        $crisis = Crisis::where('student_id', $nok->student_id)
            ->whereIn('status', ['active', 'pending'])
            ->latest('date_reported')
            ->first();

        $confirmation = DeathConfirmation::create([
            'crisis_id'            => $crisis?->crisis_id,
            'nok_id'               => $nok->nok_id,
            'student_id'           => $nok->student_id,
            'date_triggered'       => now(),
            'verified_by_kin'      => true,
            'verified_by_kin_date' => now(),
            'media_file_path'      => $path,
            'media_file_name'      => $file->getClientOriginalName(),
            'media_file_size'      => $file->getSize(),
            'admin_comments'       => $request->input('admin_comments'),
            'status'               => 'pending',
        ]);

        ActivityLog::record('nok', (string) $nok->nok_id, 'death_confirmation_submitted',
            "Submitted death confirmation #{$confirmation->confirmation_id} for student {$nok->student_id}");

        $this->notifications->send(
            recipientType: 'nok',
            recipientId:   (string) $nok->nok_id,
            email:         $nok->email,
            mailable:      new DeathConfirmationSubmittedMail($confirmation, $nok->first_name),
            notificationType: 'death_confirmation_submitted',
            subject:       'Death confirmation received',
            message:       "Your submission #{$confirmation->confirmation_id} has been received and is pending admin verification.",
            link:          route('nok.dashboard'),
            studentId:     $nok->student_id,
        );

        $admins = Admin::where('active', true)->get();
        foreach ($admins as $admin) {
            $perms = (array) ($admin->permissions ?? []);
            if (in_array('verify_death', $perms, true) || $admin->role === 'super_admin') {
                $this->notifications->logOnly(
                    recipientType: 'admin',
                    recipientId:   (string) $admin->admin_id,
                    notificationType: 'death_confirmation_pending',
                    subject:       'Pending death confirmation',
                    message:       "A death confirmation has been submitted by {$nok->full_name} for student {$nok->student_id}.",
                    link:          route('admin.death.show', $confirmation->confirmation_id),
                    studentId:     $nok->student_id,
                );
            }
        }

        return redirect()
            ->route('nok.dashboard')
            ->with('status', 'Your death confirmation has been submitted and is awaiting administrator review.');
    }

    // -----------------------------------------------------------
    // ADMIN
    // -----------------------------------------------------------

    public function adminIndex()
    {
        $pending  = DeathConfirmation::with(['nextOfKin', 'student'])
            ->where('status', 'pending')->orderByDesc('date_triggered')
            ->paginate(15, ['*'], 'pending');

        $verified = DeathConfirmation::with(['nextOfKin', 'student'])
            ->where('status', 'verified')->orderByDesc('date_confirmed')
            ->paginate(15, ['*'], 'verified');

        $rejected = DeathConfirmation::with(['nextOfKin', 'student'])
            ->where('status', 'rejected')->orderByDesc('updated_at')
            ->paginate(15, ['*'], 'rejected');

        return view('admin.death.index', compact('pending', 'verified', 'rejected'));
    }

    public function adminShow(DeathConfirmation $confirmation)
    {
        $confirmation->load('nextOfKin', 'student', 'crisis');
        return view('admin.death.show', compact('confirmation'));
    }

    public function verify(VerifyDeathRequest $request, DeathConfirmation $confirmation)
    {
        /** @var \App\Models\Admin $admin */
        $admin = Auth::guard('admin')->user();

        $perms = (array) ($admin->permissions ?? []);
        if (!in_array('verify_death', $perms, true) && $admin->role !== 'super_admin') {
            abort(403, 'You do not have permission to verify death confirmations.');
        }

        if ($confirmation->status !== 'pending') {
            return back()->withErrors(['confirmation' => 'Only pending confirmations may be processed.']);
        }

        $decision = $request->input('decision');

        if ($decision === 'verified') {
            $payload = [
                'confirmation_id' => $confirmation->confirmation_id,
                'student_id'      => $confirmation->student_id,
                'nok_id'          => $confirmation->nok_id,
                'crisis_id'       => $confirmation->crisis_id,
                'verified_by'     => $admin->admin_id,
                'verified_at'     => now()->toIso8601String(),
            ];

            $result = $this->blockchain->recordEvent(
                'DEATH_CONFIRMED',
                $payload,
                $confirmation->confirmation_id,
                'death_confirmation'
            );

            DB::transaction(function () use ($confirmation, $request, $result) {
                $confirmation->update([
                    'status'               => 'verified',
                    'date_confirmed'       => now(),
                    'admin_comments'       => $request->input('admin_comments'),
                    'blockchain_reference' => $result['hash'],
                ]);

                Student::where('student_id', $confirmation->student_id)
                    ->update(['status' => 'deceased']);

                if ($confirmation->crisis_id) {
                    Crisis::where('crisis_id', $confirmation->crisis_id)
                        ->update(['status' => 'resolved']);
                }
            });

            ActivityLog::record('admin', (string) $admin->admin_id, 'death_confirmed',
                "Verified death confirmation #{$confirmation->confirmation_id} (hash: " . substr($result['hash'], 0, 16) . '…)');

            $nok = $confirmation->nextOfKin;
            if ($nok) {
                $this->notifications->send(
                    recipientType: 'nok',
                    recipientId:   (string) $nok->nok_id,
                    email:         $nok->email,
                    mailable:      new DeathVerifiedMail($confirmation, $nok->first_name, $result['hash']),
                    notificationType: 'death_verified',
                    subject:       'Death confirmation verified',
                    message:       'The death confirmation you submitted has been verified and recorded on the blockchain.',
                    link:          route('nok.dashboard'),
                    studentId:     $confirmation->student_id,
                );
            }

            return redirect()
                ->route('admin.death.show', $confirmation->confirmation_id)
                ->with('status', 'Death confirmation verified and recorded on the blockchain.');
        }

        // Rejected
        $confirmation->update([
            'status'         => 'rejected',
            'date_confirmed' => now(),
            'admin_comments' => $request->input('admin_comments'),
        ]);

        ActivityLog::record('admin', (string) $admin->admin_id, 'death_rejected',
            "Rejected death confirmation #{$confirmation->confirmation_id}");

        $nok = $confirmation->nextOfKin;
        if ($nok) {
            $this->notifications->logOnly(
                recipientType: 'nok',
                recipientId:   (string) $nok->nok_id,
                notificationType: 'death_rejected',
                subject:       'Death confirmation update',
                message:       'Your death confirmation submission requires further review. Please contact the administrator.',
                link:          route('nok.dashboard'),
                studentId:     $confirmation->student_id,
            );
        }

        return redirect()
            ->route('admin.death.show', $confirmation->confirmation_id)
            ->with('status', 'Death confirmation marked as rejected.');
    }
}
