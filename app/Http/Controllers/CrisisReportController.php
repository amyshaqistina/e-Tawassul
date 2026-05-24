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
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

/**
 * CrisisReportController
 *
 * Owns the full crisis report lifecycle:
 *  - Student: create, store, view own report
 *  - Admin:   view one report (adminShow), verify, reject
 *
 * The admin LIST page (filterable index) is handled separately by
 * AdminCrisisController to keep that read-only listing logic isolated.
 */
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

    /**
     * Store wizard-based crisis report.
     *
     * Expected fields from new 5-step wizard:
     *  - crisis_type (medical|accident|natural_disaster|death)
     *  - sub_category (slug from Malaysia Bencana classification)
     *  - location (string)
     *  - latitude, longitude (optional, from HTML5 geolocation)
     *  - incident_date (Y-m-d)
     *  - incident_time (H:i)
     *  - crisis_description (min 30 chars)
     *  - impact_level (low|medium|high|critical)
     *  - immediate_actions (optional)
     *  - supporting_evidence[] (optional, up to 5 files, 5MB each)
     *  - consent (required, must be checked)
     */
    public function store(Request $request)
    {
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
            // Supporting evidence is now required on first submission to
            // ensure admin has documents to verify the case. Edits remain
            // flexible (the update() method keeps existing files even if
            // the user uploads nothing new).
            'supporting_evidence'   => 'required|array|min:1|max:5',
            'supporting_evidence.*' => 'file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
            'consent'               => 'accepted',
        ], [
            'consent.accepted'                  => 'You must consent to share information before submitting.',
            'crisis_description.min'            => 'Description must be at least 10 characters.',
            'incident_date.before_or_equal'     => 'The incident date cannot be in the future.',
            'supporting_evidence.required'      => 'Please upload at least one supporting document (photo, police report, medical report, etc.).',
            'supporting_evidence.min'           => 'Please upload at least one supporting document.',
            'supporting_evidence.*.mimes'       => 'Each file must be PDF, JPG, PNG, or DOC.',
            'supporting_evidence.*.max'         => 'Each file must be smaller than 5MB.',
        ]);

        /** @var \App\Models\Student $student */
        $student = Auth::guard('student')->user();

        // Combine date + time into a single datetime
        $incidentDateTime = Carbon::createFromFormat(
            'Y-m-d H:i',
            $validated['incident_date'] . ' ' . $validated['incident_time']
        );

        // Compose internal "crisis_details" payload (kept for analytics / staff review)
        $internalDetails = json_encode([
            'sub_category'      => $validated['sub_category'],
            'incident_at'       => $incidentDateTime->toIso8601String(),
            'latitude'          => $validated['latitude'] ?? null,
            'longitude'         => $validated['longitude'] ?? null,
            'immediate_actions' => $validated['immediate_actions'] ?? null,
            'submitted_from'    => $request->ip(),
            'user_agent'        => substr((string) $request->userAgent(), 0, 255),
        ], JSON_UNESCAPED_UNICODE);

        // Store uploaded files
        $evidencePaths = [];
        if ($request->hasFile('supporting_evidence')) {
            foreach ($request->file('supporting_evidence') as $file) {
                $evidencePaths[] = $file->store('crisis-evidence', 'local');
            }
        }

        $report = DB::transaction(function () use ($validated, $student, $evidencePaths, $internalDetails, $incidentDateTime) {
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
                'crisis_id'                => $crisis->crisis_id,
                'report_description'       => $validated['crisis_description'],
                'report_status'            => 'pending',
                'date_reported'            => now(),
                'supporting_evidence_path' => $evidencePaths ?: null,
            ]);
        });

        ActivityLog::record(
            'student',
            $student->student_id,
            'crisis_report_submitted',
            "Submitted crisis report #{$report->report_id} ({$validated['crisis_type']}/{$validated['sub_category']})"
        );

        $this->notifications->send(
            recipientType: 'student',
            recipientId: $student->student_id,
            email: $student->email,
            mailable: new CrisisReportSubmittedMail($report, $student->first_name),
            notificationType: 'crisis_report_submitted',
            subject: 'Crisis report received',
            message: "Your crisis report #{$report->report_id} has been received and is awaiting administrator review.",
            link: route('student.crisis.show', $report->report_id),
            studentId: $student->student_id,
        );

        $admins = Admin::where('active', true)->get();
        foreach ($admins as $admin) {
            $perms = (array) ($admin->permissions ?? []);
            if (in_array('verify_crisis', $perms, true) || $admin->role === 'super_admin') {
                $this->notifications->logOnly(
                    recipientType: 'admin',
                    recipientId: (string) $admin->admin_id,
                    notificationType: 'crisis_report_pending',
                    subject: 'New crisis report pending review',
                    message: "Student {$student->full_name} ({$student->student_id}) has submitted a new crisis report.",
                    link: route('admin.crisis.show', $report->report_id),
                    studentId: $student->student_id,
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
    // STUDENT — My Reports (full list with status filters)
    // -----------------------------------------------------------

    /**
     * List ALL reports submitted by the logged-in student, with a
     * status filter tab (all|pending|verified|rejected). Used by the
     * "My Reports" page in the student dropdown.
     */
    public function myReports(Request $request)
    {
        /** @var \App\Models\Student $student */
        $student = Auth::guard('student')->user();

        $filter = $request->query('status', 'all');
        $allowed = ['all', 'pending', 'verified', 'rejected'];
        if (!in_array($filter, $allowed, true)) {
            $filter = 'all';
        }

        $query = CrisisReport::with('crisis')
            ->where('student_id', $student->student_id)
            ->orderByDesc('date_reported');

        if ($filter !== 'all') {
            $query->where('report_status', $filter);
        }

        $reports = $query->paginate(15)->withQueryString();

        // Per-tab counts for the filter chips
        $counts = [
            'all'      => CrisisReport::where('student_id', $student->student_id)->count(),
            'pending'  => CrisisReport::where('student_id', $student->student_id)->where('report_status', 'pending')->count(),
            'verified' => CrisisReport::where('student_id', $student->student_id)->where('report_status', 'verified')->count(),
            'rejected' => CrisisReport::where('student_id', $student->student_id)->where('report_status', 'rejected')->count(),
        ];

        return view('student.reports.index', compact('reports', 'filter', 'counts'));
    }

    // -----------------------------------------------------------
    // STUDENT — Edit / Update / Delete
    // -----------------------------------------------------------
    //
    // Editing rules (this matters for blockchain integrity):
    //   pending  → fully editable; original report stays, no new row
    //   rejected → editable; on submit we treat it as a resubmission
    //              (status flips back to 'pending', re-enters admin queue)
    //   verified → LOCKED. Blockchain hash refers to specific values;
    //              changing them would break audit trail.
    //
    // Every edit is logged to activity_log with a snapshot of the change.

    public function edit(CrisisReport $report)
    {
        /** @var \App\Models\Student $student */
        $student = Auth::guard('student')->user();

        if ($report->student_id !== $student->student_id) {
            abort(403);
        }

        // VERIFIED → cannot edit (blockchain immutability)
        if ($report->report_status === 'verified') {
            return redirect()
                ->route('student.crisis.show', $report->report_id)
                ->with('error', 'Verified reports are locked by blockchain and cannot be edited. Contact welfare@iium.edu.my if something has changed.');
        }

        $report->load('crisis', 'verifier');

        // Decode crisis_details JSON for prefilling the form
        $details = [];
        if ($report->crisis && $report->crisis->crisis_details) {
            $decoded = json_decode($report->crisis->crisis_details, true);
            if (is_array($decoded)) {
                $details = $decoded;
            }
        }

        return view('student.crisis.edit', compact('report', 'details'));
    }

    public function update(Request $request, CrisisReport $report)
    {
        /** @var \App\Models\Student $student */
        $student = Auth::guard('student')->user();

        if ($report->student_id !== $student->student_id) {
            abort(403);
        }

        // Same lock rule as edit() — defense in depth
        if ($report->report_status === 'verified') {
            return redirect()
                ->route('student.crisis.show', $report->report_id)
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
        ]);

        $incidentDateTime = \Carbon\Carbon::createFromFormat(
            'Y-m-d H:i',
            $validated['incident_date'] . ' ' . $validated['incident_time']
        );

        // Merge new uploaded evidence with existing (don't replace; append)
        $evidencePaths = (array) ($report->supporting_evidence_path ?? []);
        if ($request->hasFile('supporting_evidence')) {
            foreach ($request->file('supporting_evidence') as $file) {
                $evidencePaths[] = $file->store('crisis-evidence', 'local');
            }
        }

        $wasRejected = $report->report_status === 'rejected';

        // Build updated crisis_details JSON
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
            'last_edited_by'    => 'student:' . $student->student_id,
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
                    // If it was rejected, putting the case back into the
                    // queue means resetting to 'pending'
                    'status'             => $wasRejected ? 'pending' : $report->crisis->status,
                ]);
            }

            $updates = [
                'report_description'       => $validated['crisis_description'],
                'supporting_evidence_path' => $evidencePaths ?: null,
            ];

            // RESUBMISSION — rejected reports flip back to pending so admin
            // re-reviews. We clear the verifier/timestamp/admin_remarks so
            // the previous rejection doesn't linger on the next admin view.
            // (The activity log keeps the audit trail intact.)
            if ($wasRejected) {
                $updates['report_status']        = 'pending';
                $updates['admin_verification']   = null;
                $updates['verified_at']          = null;
                $updates['admin_remarks']        = null;
            }

            $report->update($updates);
        });

        \App\Models\ActivityLog::record(
            'student',
            $report->student_id,
            $wasRejected ? 'crisis_report_resubmitted' : 'crisis_report_edited',
            $wasRejected
                ? "Resubmitted report #{$report->report_id} after rejection — back in pending queue."
                : "Edited pending report #{$report->report_id}."
        );

        // FRAUD-PATTERN ALERT — if this student now has 3+ rejected reports
        // total, log a high-priority alert so welfare admins know to review
        // them carefully. This is a non-blocking informational notification.
        if ($wasRejected) {
            $rejectionCount = CrisisReport::where('student_id', $report->student_id)
                ->where('report_status', 'rejected')
                ->count();

            // Include this resubmission's previous rejection in the count
            if (($rejectionCount + 1) >= 3) {
                $admins = \App\Models\Admin::where('active', true)->get();
                foreach ($admins as $admin) {
                    $perms = (array) ($admin->permissions ?? []);
                    if (in_array('verify_crisis', $perms, true) || $admin->role === 'super_admin') {
                        try {
                            $this->notifications->logOnly(
                                recipientType: 'admin',
                                recipientId:   (string) $admin->admin_id,
                                notificationType: 'fraud_pattern_alert',
                                subject:       'Student with multiple rejections has resubmitted',
                                message:       "Student {$student->student_id} (now {$rejectionCount}+ rejections) has resubmitted report #{$report->report_id}. Please review carefully.",
                                link:          route('admin.crisis.show', $report->report_id),
                                studentId:     $student->student_id,
                            );
                        } catch (\Throwable $e) {
                            // never block the user's resubmission on a notification error
                        }
                    }
                }
            }
        }

        $message = $wasRejected
            ? 'Your report has been resubmitted and is now awaiting admin review again. Thank you for addressing the feedback.'
            : 'Your report has been updated.';

        return redirect()
            ->route('student.crisis.show', $report->report_id)
            ->with('status', $message);
    }

    public function destroy(CrisisReport $report)
    {
        /** @var \App\Models\Student $student */
        $student = Auth::guard('student')->user();

        if ($report->student_id !== $student->student_id) {
            abort(403);
        }

        // Only pending reports can be deleted by the student. Rejected
        // ones stay (audit trail), and verified ones are blockchain-locked.
        if ($report->report_status !== 'pending') {
            return redirect()
                ->route('student.crisis.show', $report->report_id)
                ->with('error', 'Only pending reports can be deleted.');
        }

        DB::transaction(function () use ($report) {
            // Delete attached crisis row too (it was created with the report
            // in pending state, never published, never donated to)
            if ($report->crisis) {
                $report->crisis->delete();
            }
            $report->delete();
        });

        \App\Models\ActivityLog::record(
            'student',
            $student->student_id,
            'crisis_report_deleted',
            "Deleted pending crisis report #{$report->report_id}."
        );

        return redirect()
            ->route('student.reports.index')
            ->with('status', 'Pending report has been deleted.');
    }

    // -----------------------------------------------------------
    // ADMIN (single-report actions only — listing lives in AdminCrisisController)
    // -----------------------------------------------------------

    public function adminShow(CrisisReport $report)
    {
        $report->load('student', 'crisis', 'verifier');

        // Look up the student's current-semester courses and matched lecturers
        // from student_courses (populated during their iMaalum login).
        // This is what the admin sees in the "Student's Lecturers" side panel.
        $studentCourses = DB::table('student_courses')
            ->leftJoin('lecturers', 'student_courses.lecturer_id', '=', 'lecturers.lecturer_id')
            ->where('student_courses.student_id', $report->student_id)
            ->orderBy('student_courses.course_code')
            ->select(
                'student_courses.course_code',
                'student_courses.course_name',
                'student_courses.semester',
                'student_courses.lecturer_name_raw',
                'lecturers.lecturer_id',
                'lecturers.first_name',
                'lecturers.last_name',
                'lecturers.email',
                'lecturers.department',
            )
            ->get();

        // EDIT HISTORY — pull related ActivityLog rows so admin can see
        // every edit / resubmit / rejection. The action_description column
        // already contains "report #{id}" so we filter by that pattern.
        // Limited to 20 entries (typical reports have <5).
        $editHistory = \App\Models\ActivityLog::where('user_type', 'student')
            ->where('user_id', $report->student_id)
            ->whereIn('action', [
                'crisis_report_submitted',
                'crisis_report_edited',
                'crisis_report_resubmitted',
                'crisis_report_rejected',
                'crisis_report_verified',
            ])
            ->where('action_description', 'like', '%#' . $report->report_id . '%')
            ->orderBy('timestamp', 'asc')
            ->limit(20)
            ->get();

        // FRAUD SIGNAL — count prior rejections for this student so admin
        // can see "this is their 4th rejected report" at a glance.
        $studentRejectionCount = CrisisReport::where('student_id', $report->student_id)
            ->where('report_status', 'rejected')
            ->where('report_id', '!=', $report->report_id)
            ->count();

        return view('admin.crisis.show', compact(
            'report', 'studentCourses', 'editHistory', 'studentRejectionCount'
        ));
    }

        public function downloadEvidence(CrisisReport $report, int $index)
    {
        /** @var \App\Models\Admin $admin */
        $admin = Auth::guard('admin')->user();
            if (!$admin || !$admin->active) {
            abort(403);
            }

        $paths = (array) ($report->supporting_evidence_path ?? []);
        $path  = $paths[$index] ?? null;

        if (!$path || !Storage::disk('local')->exists($path)) {
            abort(404);
        }

        return Storage::disk('local')->response($path, basename($path));
    }

    public function downloadEvidenceStudent(CrisisReport $report, int $index)
{
    /** @var \App\Models\Student $student */
    $student = Auth::guard('student')->user();

    if (!$student || $report->student_id !== $student->student_id) {
        abort(403);
    }

    $paths = (array) ($report->supporting_evidence_path ?? []);
    $path  = $paths[$index] ?? null;

    if (!$path || !Storage::disk('local')->exists($path)) {
        abort(404);
    }

    return Storage::disk('local')->response($path, basename($path));
}

    public function verify(VerifyCrisisRequest $request, CrisisReport $report)
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin->active) abort(403);

        $crisis = $report->crisis;
        $payload = [
            'report_id'          => $report->report_id,
            'crisis_id'          => $report->crisis_id,
            'student_id'         => $report->student_id,
            'verified_by'        => $admin->admin_id,
            'verified_at'        => now()->toIso8601String(),
        ];

        $result = $this->blockchain->recordEvent('CRISIS_VERIFIED', $payload, $report->report_id, 'crisis_report');

        DB::transaction(function () use ($request, $report, $crisis, $admin, $result) {
            $report->update([
                'report_status'      => 'verified',
                'admin_verification' => $admin->admin_id,
                'verified_at'        => now(),
                'admin_remarks'      => $request->input('admin_remarks'),
                'blockchain_hash'    => $result['hash'],
            ]);
            if ($crisis) {
                $crisis->update(['status' => 'active']);
            }
        });

        $student = $report->student;
        if ($student) {
            $this->notifications->send(
                recipientType: 'student',
                recipientId: $student->student_id,
                email: $student->email,
                mailable: new \App\Mail\CrisisVerifiedMail($report, $student->first_name, $result['hash']),
                notificationType: 'crisis_verified',
                subject: 'Your crisis report has been verified',
                message: "Your crisis report #{$report->report_id} has been verified.",
                link: route('student.crisis.show', $report->report_id),
                studentId: $student->student_id,
            );

            // NOTIFY LECTURERS
            // Eager-load verifier + crisis so the Mailable can render
            // verified-by name and crisis type without extra DB hits per job.
            $report->loadMissing(['verifier', 'crisis']);
            $crisisType = $report->crisis->crisis_type ?? 'N/A';

            $studentCourses = DB::table('student_courses')
                ->where('student_id', $student->student_id)
                ->join('lecturers', 'student_courses.lecturer_id', '=', 'lecturers.lecturer_id')
                ->select(
                    'lecturers.email',
                    'lecturers.first_name',
                    'lecturers.last_name',
                    'student_courses.course_code',
                    'student_courses.course_name',
                    'lecturers.lecturer_id'
                )
                ->get();

            foreach ($studentCourses as $course) {
                $lecturerEmail = $course->email;
                if (app()->environment('local')) {
                    $lecturerEmail = env('TESTING_MODE_LECTURER_REDIRECT_EMAIL', 'nabilahnordin20082002@gmail.com');
                }

                $lecturerDisplayName = trim(
                    ($course->first_name ?? '') . ' ' . ($course->last_name ?? '')
                ) ?: 'Lecturer';

                $this->notifications->send(
                    recipientType: 'lecturer',
                    recipientId: (string)$course->lecturer_id,
                    email: $lecturerEmail,
                    mailable: new \App\Mail\LecturerCrisisNotificationMail(
                        report:        $report,
                        studentName:   $student->full_name,
                        courseCode:    $course->course_code,
                        lecturerName:  $lecturerDisplayName,
                        courseName:    $course->course_name ?? null,
                        studentMatric: $student->student_id ?? 'N/A',
                        crisisType:    $crisisType,
                        studentEmail:  $student->email ?? null,
                    ),
                    notificationType: 'lecturer_crisis_notified',
                    subject: "Student Crisis Notification - {$course->course_code}",
                    message: "Student {$student->full_name} has had a crisis report verified. Please consider this for their attendance.",
                    link: null,
                    studentId: $student->student_id,
                );
            }
        }

        return redirect()->route('admin.crisis.show', $report->report_id)->with('status', 'Verified and lecturers notified.');
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

        ActivityLog::record(
            'admin',
            (string) $admin->admin_id,
            'crisis_report_rejected',
            "Rejected crisis report #{$report->report_id}"
        );

        $student = $report->student;
        if ($student) {
            $this->notifications->send(
                recipientType: 'student',
                recipientId: $student->student_id,
                email: $student->email,
                mailable: new CrisisRejectedMail($report, $student->first_name, $reason),
                notificationType: 'crisis_rejected',
                subject: 'Update on your crisis report',
                message: "Your crisis report #{$report->report_id} requires additional information. Please review the administrator's notes.",
                link: route('student.crisis.show', $report->report_id),
                studentId: $student->student_id,
            );
        }

        return redirect()
            ->route('admin.crisis.show', $report->report_id)
            ->with('status', 'Crisis report rejected and the student has been notified.');
    }
}
