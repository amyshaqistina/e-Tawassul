<?php

namespace App\Http\Controllers;

use App\Http\Requests\RejectCrisisRequest;
use App\Http\Requests\SubmitCrisisReportRequest;
use App\Http\Requests\VerifyCrisisRequest;
use App\Mail\CrisisRejectedMail;
use App\Mail\CrisisReportSubmittedMail;
use App\Mail\CrisisVerifiedMail;
use App\Models\ActivityLog;
use App\Models\Admin;
use App\Models\Crisis;
use App\Models\CrisisReport;
use App\Services\BlockchainService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CrisisReportController extends Controller
{
    public function __construct(
        protected BlockchainService $blockchain,
        protected NotificationService $notifications,
    ) {}

    // -----------------------------------------------------------
    // STUDENT
    // -----------------------------------------------------------

    public function create()
    {
        return view('student.crisis.create');
    }

    public function store(SubmitCrisisReportRequest $request)
    {
        /** @var \App\Models\Student $student */
        $student = Auth::guard('student')->user();

        $evidencePaths = [];
        if ($request->hasFile('supporting_evidence')) {
            foreach ($request->file('supporting_evidence') as $file) {
                $evidencePaths[] = $file->store('crisis-evidence', 'local');
            }
        }

        $report = DB::transaction(function () use ($request, $student, $evidencePaths) {
            $crisis = Crisis::create([
                'crisis_type'        => $request->input('crisis_type'),
                'crisis_description' => $request->input('crisis_description'),
                'crisis_details'     => $request->input('crisis_details'),
                'impact_level'       => $request->input('impact_level'),
                'location'           => $request->input('location'),
                'date_reported'      => now(),
                'status'             => 'pending',
                'donation_target'    => $request->input('donation_target', 0),
                'donation_raised'    => 0,
                'student_id'         => $student->student_id,
            ]);

            return CrisisReport::create([
                'student_id'               => $student->student_id,
                'crisis_id'                => $crisis->crisis_id,
                'report_description'       => $request->input('report_description'),
                'report_status'            => 'pending',
                'date_reported'            => now(),
                'supporting_evidence_path' => $evidencePaths ?: null,
            ]);
        });

        ActivityLog::record('student', $student->student_id, 'crisis_report_submitted',
            "Submitted crisis report #{$report->report_id}");

        $this->notifications->send(
            recipientType: 'student',
            recipientId:   $student->student_id,
            email:         $student->email,
            mailable:      new CrisisReportSubmittedMail($report, $student->first_name),
            notificationType: 'crisis_report_submitted',
            subject:       'Crisis report received',
            message:       "Your crisis report #{$report->report_id} has been received and is awaiting administrator review.",
            link:          route('student.crisis.show', $report->report_id),
            studentId:     $student->student_id,
        );

        $admins = Admin::where('active', true)->get();
        foreach ($admins as $admin) {
            $perms = (array) ($admin->permissions ?? []);
            if (in_array('verify_crisis', $perms, true) || $admin->role === 'super_admin') {
                $this->notifications->logOnly(
                    recipientType: 'admin',
                    recipientId:   (string) $admin->admin_id,
                    notificationType: 'crisis_report_pending',
                    subject:       'New crisis report pending review',
                    message:       "Student {$student->full_name} ({$student->student_id}) has submitted a new crisis report.",
                    link:          route('admin.crisis.show', $report->report_id),
                    studentId:     $student->student_id,
                );
            }
        }

        return redirect()
            ->route('student.crisis.show', $report->report_id)
            ->with('status', 'Your crisis report has been submitted for review.');
    }

    public function show(CrisisReport $report)
    {
        /** @var \App\Models\Student $student */
        $student = Auth::guard('student')->user();

        if ($report->student_id !== $student->student_id) {
            abort(403);
        }

        $report->load('crisis', 'verifier');
        return view('student.crisis.show', compact('report'));
    }

    // -----------------------------------------------------------
    // ADMIN
    // -----------------------------------------------------------

    public function adminIndex(Request $request)
    {
        $tab = $request->query('tab', 'pending');

        $pending = CrisisReport::with(['student', 'crisis'])
            ->where('report_status', 'pending')
            ->orderByDesc('date_reported')
            ->paginate(15, ['*'], 'pending');

        $verified = CrisisReport::with(['student', 'crisis'])
            ->where('report_status', 'verified')
            ->orderByDesc('verified_at')
            ->paginate(15, ['*'], 'verified');

        $rejected = CrisisReport::with(['student', 'crisis'])
            ->where('report_status', 'rejected')
            ->orderByDesc('updated_at')
            ->paginate(15, ['*'], 'rejected');

        return view('admin.crisis.index', compact('tab', 'pending', 'verified', 'rejected'));
    }

    public function adminShow(CrisisReport $report)
    {
        $report->load('student', 'crisis', 'verifier');
        return view('admin.crisis.show', compact('report'));
    }

    public function verify(VerifyCrisisRequest $request, CrisisReport $report)
    {
        /** @var \App\Models\Admin $admin */
        $admin = Auth::guard('admin')->user();

        if (!$admin->active) abort(403, 'Inactive admin account.');
        $perms = (array) ($admin->permissions ?? []);
        if (!in_array('verify_crisis', $perms, true) && $admin->role !== 'super_admin') {
            abort(403, 'You do not have permission to verify crisis reports.');
        }

        if ($report->report_status !== 'pending') {
            return back()->withErrors(['report' => 'Only pending reports may be verified.']);
        }

        $crisis = $report->crisis;

        $payload = [
            'report_id'          => $report->report_id,
            'crisis_id'          => $report->crisis_id,
            'student_id'         => $report->student_id,
            'crisis_type'        => $crisis?->crisis_type,
            'report_description' => $report->report_description,
            'verified_by'        => $admin->admin_id,
            'verified_at'        => now()->toIso8601String(),
        ];

        $result = $this->blockchain->recordEvent(
            'CRISIS_VERIFIED',
            $payload,
            $report->report_id,
            'crisis_report'
        );

        DB::transaction(function () use ($request, $report, $crisis, $admin, $result) {
            $report->update([
                'report_status'      => 'verified',
                'admin_verification' => $admin->admin_id,
                'verified_at'        => now(),
                'admin_remarks'      => $request->input('admin_remarks'),
                'blockchain_hash'    => $result['hash'],
            ]);

            if ($crisis) {
                $updates = ['status' => 'active'];
                if ($request->filled('donation_target')) {
                    $updates['donation_target'] = $request->input('donation_target');
                }
                if ($request->filled('impact_level')) {
                    $updates['impact_level'] = $request->input('impact_level');
                }
                $crisis->update($updates);
            }
        });

        ActivityLog::record('admin', (string) $admin->admin_id, 'crisis_report_verified',
            "Verified crisis report #{$report->report_id} (hash: " . substr($result['hash'], 0, 16) . '…)');

        $student = $report->student;
        if ($student) {
            $this->notifications->send(
                recipientType: 'student',
                recipientId:   $student->student_id,
                email:         $student->email,
                mailable:      new CrisisVerifiedMail($report, $student->first_name, $result['hash']),
                notificationType: 'crisis_verified',
                subject:       'Your crisis report has been verified',
                message:       "Your crisis report #{$report->report_id} has been verified and is now active. Blockchain hash: " . substr($result['hash'], 0, 16) . '…',
                link:          route('student.crisis.show', $report->report_id),
                studentId:     $student->student_id,
            );
        }

        return redirect()
            ->route('admin.crisis.show', $report->report_id)
            ->with('status', 'Crisis report verified and recorded on the blockchain.');
    }

    public function reject(RejectCrisisRequest $request, CrisisReport $report)
    {
        /** @var \App\Models\Admin $admin */
        $admin = Auth::guard('admin')->user();

        $perms = (array) ($admin->permissions ?? []);
        if (!in_array('verify_crisis', $perms, true) && $admin->role !== 'super_admin') {
            abort(403);
        }

        if ($report->report_status !== 'pending') {
            return back()->withErrors(['report' => 'Only pending reports may be rejected.']);
        }

        $reason = $request->input('admin_remarks');

        $payload = [
            'report_id'    => $report->report_id,
            'crisis_id'    => $report->crisis_id,
            'student_id'   => $report->student_id,
            'rejected_by'  => $admin->admin_id,
            'reason'       => $reason,
            'rejected_at'  => now()->toIso8601String(),
        ];

        $result = $this->blockchain->recordEvent(
            'REPORT_REJECTED',
            $payload,
            $report->report_id,
            'crisis_report'
        );

        DB::transaction(function () use ($report, $admin, $reason, $result) {
            $report->update([
                'report_status'      => 'rejected',
                'admin_verification' => $admin->admin_id,
                'verified_at'        => now(),
                'admin_remarks'      => $reason,
                'blockchain_hash'    => $result['hash'],
            ]);

            if ($report->crisis) {
                $report->crisis->update(['status' => 'closed']);
            }
        });

        ActivityLog::record('admin', (string) $admin->admin_id, 'crisis_report_rejected',
            "Rejected crisis report #{$report->report_id}");

        $student = $report->student;
        if ($student) {
            $this->notifications->send(
                recipientType: 'student',
                recipientId:   $student->student_id,
                email:         $student->email,
                mailable:      new CrisisRejectedMail($report, $student->first_name, $reason),
                notificationType: 'crisis_rejected',
                subject:       'Update on your crisis report',
                message:       "Your crisis report #{$report->report_id} requires additional information. Please review the administrator's notes.",
                link:          route('student.crisis.show', $report->report_id),
                studentId:     $student->student_id,
            );
        }

        return redirect()
            ->route('admin.crisis.show', $report->report_id)
            ->with('status', 'Crisis report rejected and the student has been notified.');
    }
}
