<?php

namespace App\Http\Controllers;

use App\Mail\CrisisReportSubmittedMail;
use App\Models\ActivityLog;
use App\Models\Admin;
use App\Models\Crisis;
use App\Models\CrisisReport;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * NokCrisisController
 *
 * Allows a verified Next-of-Kin to submit a crisis report on behalf of their
 * linked student. The lifecycle then mirrors a student-filed report:
 *  - status starts as 'pending'
 *  - admins verify/reject via the existing CrisisReportController
 *
 * Submissions are flagged via crisis_report.submitted_by_nok = true so admins
 * (and the NOK themselves) can distinguish them at a glance.
 */
class NokCrisisController extends Controller
{
    public function __construct(
        protected NotificationService $notifications,
    ) {}

    /**
     * Show the crisis-report wizard for a NOK.
     */
    public function create()
    {
        /** @var \App\Models\NextOfKin $nok */
        $nok = Auth::guard('nok')->user();
        $nok->load('student');

        abort_unless($nok->student, 403, 'You are not linked to any student.');

        return view('nok.crisis.create', [
            'nok'     => $nok,
            'student' => $nok->student,
        ]);
    }

    /**
     * Persist a NOK-submitted crisis report. Validation mirrors the student
     * wizard so the same JS client works against either endpoint.
     */
    public function store(Request $request)
    {
        /** @var \App\Models\NextOfKin $nok */
        $nok = Auth::guard('nok')->user();
        $nok->load('student');

        $student = $nok->student;
        abort_unless($student, 403, 'You are not linked to any student.');

        $validated = $request->validate([
            'crisis_type'           => 'required|in:medical,accident,natural_disaster,death',
            'sub_category'          => 'required|string|max:100',
            'location'              => 'required|string|max:500',
            'latitude'              => 'nullable|numeric|between:-90,90',
            'longitude'             => 'nullable|numeric|between:-180,180',
            'incident_date'         => 'required|date|before_or_equal:today',
            'incident_time'         => 'required|date_format:H:i',
            'crisis_description'    => 'required|string|min:10|max:2000',
            'impact_level'          => 'required|in:low,medium,high,critical',
            'immediate_actions'     => 'nullable|string|max:1000',
            // Supporting evidence required on first submission so admin can
            // verify the case without a rejection-resubmission cycle.
            'supporting_evidence'   => 'required|array|min:1|max:5',
            'supporting_evidence.*' => 'file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
            'consent'               => 'accepted',
        ], [
            'consent.accepted'              => 'You must consent to share information before submitting.',
            'crisis_description.min'        => 'Description must be at least 10 characters.',
            'incident_date.before_or_equal' => 'The incident date cannot be in the future.',
            'supporting_evidence.required'  => 'Please upload at least one supporting document (photo, police report, medical report, etc.).',
            'supporting_evidence.min'       => 'Please upload at least one supporting document.',
            'supporting_evidence.*.mimes'   => 'Each file must be PDF, JPG, PNG, or DOC.',
            'supporting_evidence.*.max'     => 'Each file must be smaller than 5MB.',
        ]);

        $incidentDateTime = Carbon::createFromFormat(
            'Y-m-d H:i',
            $validated['incident_date'] . ' ' . $validated['incident_time']
        );

        $internalDetails = json_encode([
            'sub_category'      => $validated['sub_category'],
            'incident_at'       => $incidentDateTime->toIso8601String(),
            'latitude'          => $validated['latitude'] ?? null,
            'longitude'         => $validated['longitude'] ?? null,
            'immediate_actions' => $validated['immediate_actions'] ?? null,
            'submitted_by'      => 'nok',
            'nok_id'            => $nok->nok_id,
            'nok_name'          => trim($nok->first_name . ' ' . $nok->last_name),
            'submitted_from'    => $request->ip(),
            'user_agent'        => substr((string) $request->userAgent(), 0, 255),
        ], JSON_UNESCAPED_UNICODE);

        $evidencePaths = [];
        if ($request->hasFile('supporting_evidence')) {
            foreach ($request->file('supporting_evidence') as $file) {
                $evidencePaths[] = $file->store('crisis-evidence', 'local');
            }
        }

        $report = DB::transaction(function () use ($validated, $student, $nok, $evidencePaths, $internalDetails, $incidentDateTime) {
            $crisis = Crisis::create([
                'crisis_type'        => $validated['crisis_type'],
                'crisis_description' => $validated['crisis_description'],
                'crisis_details'     => $internalDetails,
                'impact_level'       => $validated['impact_level'],
                'location'           => $validated['location'],
                'latitude'           => $validated['latitude'] ?? null,
                'longitude'          => $validated['longitude'] ?? null,
                'sub_category'       => $validated['sub_category'],
                'incident_at'        => $incidentDateTime,
                'date_reported'      => now(),
                'status'             => 'pending',
                'donation_target'    => 0,
                'donation_raised'    => 0,
                'student_id'         => $student->student_id,
            ]);

            return CrisisReport::create([
                'student_id'               => $student->student_id,
                'nok_id'                   => $nok->nok_id,
                'submitted_by_nok'         => true,
                'crisis_id'                => $crisis->crisis_id,
                'report_description'       => $validated['crisis_description'],
                'report_status'            => 'pending',
                'date_reported'            => now(),
                'supporting_evidence_path' => $evidencePaths ?: null,
            ]);
        });

        ActivityLog::record(
            'nok',
            (string) $nok->nok_id,
            'crisis_report_submitted_by_nok',
            "NOK {$nok->first_name} submitted crisis report #{$report->report_id} on behalf of student {$student->student_id} ({$validated['crisis_type']}/{$validated['sub_category']})"
        );

        // Notify the NOK themselves (acknowledgement of receipt).
        $this->notifications->send(
            recipientType:    'nok',
            recipientId:      (string) $nok->nok_id,
            email:            $nok->email,
            mailable:         new CrisisReportSubmittedMail($report, $nok->first_name),
            notificationType: 'crisis_report_submitted',
            subject:          'Crisis report received',
            message:          "Your crisis report #{$report->report_id} (submitted on behalf of {$student->full_name}) has been received and is awaiting administrator review.",
            link:             route('nok.crisis.show', $report->report_id),
            studentId:        $student->student_id,
        );

        // Notify the student that someone reported a crisis on their behalf.
        if ($student->email) {
            $this->notifications->logOnly(
                recipientType:    'student',
                recipientId:      $student->student_id,
                notificationType: 'crisis_report_filed_by_nok',
                subject:          'A crisis report was filed on your behalf',
                message:          "Your next of kin ({$nok->first_name}) submitted a crisis report on your behalf. Report #{$report->report_id} is now awaiting administrator review.",
                link:             route('student.crisis.show', $report->report_id),
                studentId:        $student->student_id,
            );
        }

        // Notify admins (same routing as student-submitted reports).
        $admins = Admin::where('active', true)->get();
        foreach ($admins as $admin) {
            $perms = (array) ($admin->permissions ?? []);
            if (in_array('verify_crisis', $perms, true) || $admin->role === 'super_admin') {
                $this->notifications->logOnly(
                    recipientType:    'admin',
                    recipientId:      (string) $admin->admin_id,
                    notificationType: 'crisis_report_pending',
                    subject:          'New crisis report pending review (filed by NOK)',
                    message:          "Next of kin {$nok->first_name} {$nok->last_name} submitted a new crisis report on behalf of student {$student->full_name} ({$student->student_id}).",
                    link:             route('admin.crisis.show', $report->report_id),
                    studentId:        $student->student_id,
                );
            }
        }

        return redirect()
            ->route('nok.crisis.show', $report->report_id)
            ->with('status', 'Your crisis report has been submitted for review.');
    }

    /**
     * Show a NOK-submitted crisis report. NOKs may only view reports they
     * personally submitted — not reports the student filed themselves.
     */
    public function show(CrisisReport $report)
    {
        /** @var \App\Models\NextOfKin $nok */
        $nok = Auth::guard('nok')->user();

        if (!$report->submitted_by_nok || $report->nok_id !== $nok->nok_id) {
            abort(403);
        }

        $report->load('crisis', 'verifier', 'student');
        return view('nok.crisis.show', compact('report'));
    }

    /**
     * Show edit form. Mirrors student edit:
     *   - pending  → editable
     *   - rejected → editable + resubmit (status flips back to pending on save)
     *   - verified → 403 (blockchain integrity)
     */
    public function edit(CrisisReport $report)
    {
        /** @var \App\Models\NextOfKin $nok */
        $nok = Auth::guard('nok')->user();

        // Only the NOK who submitted may edit
        if (!$report->submitted_by_nok || $report->nok_id !== $nok->nok_id) {
            abort(403);
        }

        // VERIFIED → cannot edit (blockchain immutability)
        if ($report->report_status === 'verified') {
            return redirect()
                ->route('nok.crisis.show', $report->report_id)
                ->with('error', 'Verified reports are locked by blockchain and cannot be edited. Contact welfare@iium.edu.my if something has changed.');
        }

        $report->load('crisis', 'verifier', 'student');

        // Decode crisis_details JSON for prefilling the form
        $details = [];
        if ($report->crisis && $report->crisis->crisis_details) {
            $decoded = json_decode($report->crisis->crisis_details, true);
            if (is_array($decoded)) {
                $details = $decoded;
            }
        }

        return view('nok.crisis.edit', compact('report', 'details'));
    }

    /**
     * Persist edits. Rejected reports flip back to pending on save so admin
     * re-reviews; pending reports stay pending. Verified reports are blocked.
     */
    public function update(Request $request, CrisisReport $report)
    {
        /** @var \App\Models\NextOfKin $nok */
        $nok = Auth::guard('nok')->user();

        if (!$report->submitted_by_nok || $report->nok_id !== $nok->nok_id) {
            abort(403);
        }

        // Defense in depth — same lock check as edit()
        if ($report->report_status === 'verified') {
            return redirect()
                ->route('nok.crisis.show', $report->report_id)
                ->with('error', 'Verified reports are locked and cannot be edited.');
        }

        $validated = $request->validate([
            'crisis_type'           => 'required|in:medical,accident,natural_disaster,death',
            'sub_category'          => 'required|string|max:100',
            'location'              => 'required|string|max:500',
            'incident_date'         => 'required|date|before_or_equal:today',
            'incident_time'         => 'required|date_format:H:i',
            'crisis_description'    => 'required|string|min:10|max:2000',
            'impact_level'          => 'required|in:low,medium,high,critical',
            'immediate_actions'     => 'nullable|string|max:1000',
            'supporting_evidence'   => 'nullable|array|max:5',
            'supporting_evidence.*' => 'file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
        ], [
            'crisis_description.min'        => 'Description must be at least 10 characters.',
            'incident_date.before_or_equal' => 'The incident date cannot be in the future.',
            'supporting_evidence.*.mimes'   => 'Each file must be PDF, JPG, PNG, or DOC.',
            'supporting_evidence.*.max'     => 'Each file must be smaller than 5MB.',
        ]);

        $incidentDateTime = Carbon::createFromFormat(
            'Y-m-d H:i',
            $validated['incident_date'] . ' ' . $validated['incident_time']
        );

        // Append new uploaded evidence to existing — never replace
        $evidencePaths = (array) ($report->supporting_evidence_path ?? []);
        if ($request->hasFile('supporting_evidence')) {
            foreach ($request->file('supporting_evidence') as $file) {
                $evidencePaths[] = $file->store('crisis-evidence', 'local');
            }
        }

        $wasRejected = $report->report_status === 'rejected';

        // Merge into existing crisis_details JSON (preserves original sub_category etc.
        // we don't know about) plus update the editable fields and bookkeeping.
        $existingDetails = [];
        if ($report->crisis && $report->crisis->crisis_details) {
            $decoded = json_decode($report->crisis->crisis_details, true);
            if (is_array($decoded)) {
                $existingDetails = $decoded;
            }
        }
        $updatedDetails = array_merge($existingDetails, [
            'sub_category'      => $validated['sub_category'],
            'incident_at'       => $incidentDateTime->toIso8601String(),
            'immediate_actions' => $validated['immediate_actions'] ?? null,
            'last_edited_at'    => now()->toIso8601String(),
            'last_edited_by'    => 'nok:' . $nok->nok_id,
        ]);
        $internalDetails = json_encode($updatedDetails, JSON_UNESCAPED_UNICODE);

        DB::transaction(function () use ($report, $validated, $evidencePaths, $internalDetails, $incidentDateTime, $wasRejected) {
            if ($report->crisis) {
                $report->crisis->update([
                    'crisis_type'        => $validated['crisis_type'],
                    'crisis_description' => $validated['crisis_description'],
                    'crisis_details'     => $internalDetails,
                    'impact_level'       => $validated['impact_level'],
                    'location'           => $validated['location'],
                    'sub_category'       => $validated['sub_category'],
                    'incident_at'        => $incidentDateTime,
                    'status'             => $wasRejected ? 'pending' : $report->crisis->status,
                ]);
            }

            $updates = [
                'report_description'       => $validated['crisis_description'],
                'supporting_evidence_path' => $evidencePaths ?: null,
            ];

            // RESUBMISSION — rejected → back to pending, clear previous admin decision
            // so admin re-reviews fresh. Activity log preserves the audit trail.
            if ($wasRejected) {
                $updates['report_status']      = 'pending';
                $updates['admin_verification'] = null;
                $updates['verified_at']        = null;
                $updates['admin_remarks']      = null;
            }

            $report->update($updates);
        });

        ActivityLog::record(
            'nok',
            (string) $nok->nok_id,
            $wasRejected ? 'nok_crisis_report_resubmitted' : 'nok_crisis_report_edited',
            $wasRejected
                ? "NOK resubmitted report #{$report->report_id} after rejection — back in pending queue."
                : "NOK edited pending report #{$report->report_id}."
        );

        // Notify admins on resubmission (matches student behavior)
        if ($wasRejected) {
            try {
                $admins = Admin::where('active', true)->get();
                foreach ($admins as $admin) {
                    $perms = (array) ($admin->permissions ?? []);
                    if (in_array('verify_crisis', $perms, true) || $admin->role === 'super_admin') {
                        $this->notifications->logOnly(
                            recipientType: 'admin',
                            recipientId:   (string) $admin->admin_id,
                            notificationType: 'crisis_report_resubmitted',
                            subject:       'NOK has resubmitted a rejected crisis report',
                            message:       "NOK {$nok->nok_id} has resubmitted report #{$report->report_id} for re-review.",
                            link:          route('admin.crisis.show', $report->report_id),
                            studentId:     $report->student_id,
                        );
                    }
                }
            } catch (\Throwable $e) {
                // never block the resubmission on a notification failure
            }
        }

        $message = $wasRejected
            ? 'Your report has been resubmitted and is now awaiting admin review again. Thank you for addressing the feedback.'
            : 'Your report has been updated.';

        return redirect()
            ->route('nok.crisis.show', $report->report_id)
            ->with('status', $message);
    }

    /**
     * Delete a pending NOK-submitted report. Verified and rejected reports
     * cannot be deleted (verified = blockchain locked, rejected = preserves
     * audit trail so admin can see fraud patterns).
     */
    public function destroy(CrisisReport $report)
    {
        /** @var \App\Models\NextOfKin $nok */
        $nok = Auth::guard('nok')->user();

        if (!$report->submitted_by_nok || $report->nok_id !== $nok->nok_id) {
            abort(403);
        }

        if ($report->report_status !== 'pending') {
            return redirect()
                ->route('nok.crisis.show', $report->report_id)
                ->with('error', 'Only pending reports can be deleted.');
        }

        $reportId = $report->report_id;
        $studentId = $report->student_id;

        DB::transaction(function () use ($report) {
            // Delete the attached crisis row first (FK cascade should also do this,
            // but be explicit to keep activity_log accurate)
            if ($report->crisis) {
                $report->crisis->delete();
            }
            $report->delete();
        });

        ActivityLog::record(
            'nok',
            (string) $nok->nok_id,
            'nok_crisis_report_deleted',
            "NOK deleted pending report #{$reportId} (for student {$studentId})."
        );

        return redirect()
            ->route('nok.submissions.index')
            ->with('status', 'Report deleted.');
    }
}
