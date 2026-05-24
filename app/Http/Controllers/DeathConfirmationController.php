<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubmitDeathConfirmationRequest;
use App\Http\Requests\VerifyDeathRequest;
use App\Mail\DeathConfirmationSubmittedMail;
use App\Mail\DeathVerifiedMail;
use App\Mail\LecturerDeathNotificationMail;
use App\Models\ActivityLog;
use App\Models\Admin;
use App\Models\Crisis;
use App\Models\DeathConfirmation;
use App\Models\Student;
use App\Services\BlockchainService;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DeathConfirmationController extends Controller
{
    public function __construct(
        protected BlockchainService $blockchain,
        protected NotificationService $notifications,
    ) {}

    // -----------------------------------------------------------
    // NOK — submit
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
    // NOK — view a submitted confirmation
    // -----------------------------------------------------------

    public function nokShow(DeathConfirmation $confirmation)
    {
        /** @var \App\Models\NextOfKin $nok */
        $nok = Auth::guard('nok')->user();

        if ((string) $confirmation->nok_id !== (string) $nok->nok_id) {
            abort(403, 'You can only view confirmations that you submitted.');
        }

        $confirmation->load('nextOfKin', 'student');

        $releasedLdms = \App\Models\Ldms::where('student_id', $confirmation->student_id)
            ->where('is_released', true)
            ->orderByDesc('date_triggered')
            ->get();

        return view('nok.death.show', compact('confirmation', 'releasedLdms'));
    }

    // -----------------------------------------------------------
    // ADMIN — list
    // -----------------------------------------------------------

    public function adminIndex(Request $request)
    {
        $tab = in_array($request->query('tab'), ['pending', 'verified', 'rejected'], true)
            ? $request->query('tab')
            : 'pending';

        $applyFilters = function ($query) use ($request) {
            if ($s = trim((string) $request->query('search'))) {
                $query->where(function ($w) use ($s) {
                    $w->where('death_confirmation.confirmation_id', 'like', "%{$s}%")
                      ->orWhere('death_confirmation.student_id', 'like', "%{$s}%")
                      ->orWhereHas('nextOfKin', function ($nq) use ($s) {
                          $nq->where('first_name', 'like', "%{$s}%")
                             ->orWhere('last_name', 'like', "%{$s}%")
                             ->orWhere('email', 'like', "%{$s}%");
                      })
                      ->orWhereHas('student', function ($sq) use ($s) {
                          $sq->where('student_id', 'like', "%{$s}%");
                      });
                });
            }

            [$from, $to] = $this->resolveDateRange($request);
            if ($from) $query->whereDate('death_confirmation.date_triggered', '>=', $from);
            if ($to)   $query->whereDate('death_confirmation.date_triggered', '<=', $to);

            if ($request->query('has_doc') === 'yes') {
                $query->whereNotNull('media_file_path');
            } elseif ($request->query('has_doc') === 'no') {
                $query->whereNull('media_file_path');
            }

            return $query;
        };

        $pending = $applyFilters(
            DeathConfirmation::with(['student', 'nextOfKin'])
                ->where('status', 'pending')
                ->orderByDesc('date_triggered')
        )->paginate(15, ['*'], 'pending')->withQueryString();

        $verified = $applyFilters(
            DeathConfirmation::with(['student', 'nextOfKin'])
                ->where('status', 'verified')
                ->orderByDesc('date_confirmed')
        )->paginate(15, ['*'], 'verified')->withQueryString();

        $rejected = $applyFilters(
            DeathConfirmation::with(['student', 'nextOfKin'])
                ->where('status', 'rejected')
                ->orderByDesc('updated_at')
        )->paginate(15, ['*'], 'rejected')->withQueryString();

        return view('admin.death.index', compact('tab', 'pending', 'verified', 'rejected'));
    }

    public function adminShow(DeathConfirmation $confirmation)
    {
        $confirmation->load('nextOfKin', 'student', 'crisis');

        $studentCourses = DB::table('student_courses')
            ->leftJoin('lecturers', 'student_courses.lecturer_id', '=', 'lecturers.lecturer_id')
            ->where('student_courses.student_id', $confirmation->student_id)
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

        return view('admin.death.show', compact('confirmation', 'studentCourses'));
    }

    /**
     * Stream the death confirmation supporting document to an admin.
     *
     * Files are stored on the 'local' disk under storage/app/death-confirmations/
     * (see store() above). This action streams the bytes back with strict
     * authorization: admin must be active and have 'verify_death' permission
     * (or be super_admin).
     */
    public function downloadDocument(DeathConfirmation $confirmation)
    {
        /** @var \App\Models\Admin $admin */
        $admin = Auth::guard('admin')->user();
        if (!$admin || !$admin->active) {
            abort(403);
        }

        $perms = (array) ($admin->permissions ?? []);
        if (!in_array('verify_death', $perms, true) && $admin->role !== 'super_admin') {
            abort(403);
        }

        if (!$confirmation->media_file_path) {
            abort(404, 'No document attached to this confirmation.');
        }

        $disk = Storage::disk('local');
        if (!$disk->exists($confirmation->media_file_path)) {
            abort(404, 'Document file is missing from storage.');
        }

        $downloadName = $confirmation->media_file_name
            ?: basename($confirmation->media_file_path);

        ActivityLog::record('admin', (string) $admin->admin_id, 'death_doc_viewed',
            "Viewed supporting document for confirmation #{$confirmation->confirmation_id}");

        return $disk->response(
            $confirmation->media_file_path,
            $downloadName
        );
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
                    // Mark the linked crisis as resolved AND auto-close
                    // its public donation page. Donors should not be
                    // able to keep donating after a death has been
                    // formally verified — out of respect, and because
                    // funds may now need to be redirected/handled by
                    // the next of kin. Admin can still re-open from
                    // the crisis page if funeral costs require it.
                    Crisis::where('crisis_id', $confirmation->crisis_id)
                        ->update([
                            'status'                 => 'resolved',
                            'donation_open'          => false,
                            'donation_closed_at'     => now(),
                            'donation_closed_reason' => 'Case resolved — death verified',
                        ]);
                }
            });

            ActivityLog::record('admin', (string) $admin->admin_id, 'death_confirmed',
                "Verified death confirmation #{$confirmation->confirmation_id} (hash: " . substr($result['hash'], 0, 16) . '…)');

            // Log the donation auto-close so admin can audit when/why
            // a case's donation page went dark.
            if ($confirmation->crisis_id) {
                ActivityLog::record('admin', (string) $admin->admin_id, 'donation_auto_closed',
                    "Auto-closed donations for crisis #{$confirmation->crisis_id} (death verified)");
            }

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

            $student = Student::where('student_id', $confirmation->student_id)->first();
            if ($student) {
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
                        recipientId:   (string) $course->lecturer_id,
                        email:         $lecturerEmail,
                        mailable:      new LecturerDeathNotificationMail(
                            confirmation:   $confirmation,
                            studentName:    $student->full_name,
                            courseCode:     $course->course_code,
                            lecturerName:   $lecturerDisplayName,
                            courseName:     $course->course_name ?? null,
                            studentMatric:  $student->student_id ?? 'N/A',
                        ),
                        notificationType: 'lecturer_death_notified',
                        subject:       "Student Bereavement - {$course->course_code}",
                        message:       "Student {$student->full_name} has passed away. Please update your class records.",
                        link:          null,
                        studentId:     $student->student_id,
                    );
                }
            }

            return redirect()
                ->route('admin.death.show', $confirmation->confirmation_id)
                ->with('status', 'Death confirmation verified. NoK and lecturers have been notified.');
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

    // -----------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------

    protected function resolveDateRange(Request $request): array
    {
        switch ($request->query('date_range')) {
            case 'today':
                return [Carbon::today(), Carbon::today()->endOfDay()];
            case 'week':
                return [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()];
            case 'last_week':
                return [Carbon::now()->subWeek()->startOfWeek(), Carbon::now()->subWeek()->endOfWeek()];
            case 'month':
                return [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()];
            case 'custom':
                $from = $request->query('date_from') ? Carbon::parse($request->query('date_from'))->startOfDay() : null;
                $to   = $request->query('date_to')   ? Carbon::parse($request->query('date_to'))->endOfDay()   : null;
                return [$from, $to];
            default:
                return [null, null];
        }
    }
}
