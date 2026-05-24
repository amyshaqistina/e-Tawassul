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
}
