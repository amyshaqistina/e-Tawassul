<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateLDMSRequest;
use App\Http\Requests\UpdateLDMSRequest;
use App\Mail\LDMSReleasedMail;
use App\Models\ActivityLog;
use App\Models\Ldms;
use App\Models\NextOfKin;
use App\Services\BlockchainService;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LDMSController extends Controller
{
    /**
     * The Flysystem disk that stores LDMS attachments with transparent
     * AES-256-CBC encryption.  Configured in config/filesystems.php as
     * 'ldms_secure' (driver: local, encrypt: true).
     */
    protected const SECURE_DISK = 'ldms_secure';

    public function __construct(
        protected BlockchainService $blockchain,
        protected NotificationService $notifications,
    ) {}

    // -----------------------------------------------------------
    // STUDENT
    // -----------------------------------------------------------

    public function index()
    {
        /** @var \App\Models\Student $student */
        $student = Auth::guard('student')->user();

        $messages = Ldms::where('student_id', $student->student_id)
            ->orderByDesc('updated_at')
            ->paginate(10);

        return view('student.ldms.index', compact('messages'));
    }

    public function create()
    {
        return view('student.ldms.create');
    }

    public function store(CreateLDMSRequest $request)
    {
        /** @var \App\Models\Student $student */
        $student = Auth::guard('student')->user();

        $mediaPaths = $this->storeMediaFiles($request);

        // NB: message_content is automatically AES-256-CBC encrypted by
        //     the 'encrypted' cast on the Ldms model.  We just pass the
        //     plaintext through and Eloquent handles the rest.
        $ldms = Ldms::create([
            'student_id'       => $student->student_id,
            'message_content'  => $request->input('message_content'),
            'media_type'       => $request->input('media_type', 'text'),
            'media_file_path'  => $mediaPaths ?: null,
            'is_released'      => false,
            'triggered_by_kin' => false,
        ]);

        ActivityLog::record('student', $student->student_id, 'ldms_created',
            "Created LDMS #{$ldms->ldms_id}");

        return redirect()
            ->route('student.ldms.index')
            ->with('status', 'Your message has been securely saved and encrypted.');
    }

    public function edit(Ldms $ldms)
    {
        $this->authorizeStudent($ldms);
        return view('student.ldms.edit', compact('ldms'));
    }

    public function update(UpdateLDMSRequest $request, Ldms $ldms)
    {
        $this->authorizeStudent($ldms);

        if ($ldms->is_released) {
            return back()->withErrors(['ldms' => 'Released messages cannot be edited.']);
        }

        $existing = (array) ($ldms->media_file_path ?? []);

        // 1) Remove any files the student ticked "Delete" on in the edit form.
        $toRemove = (array) $request->input('remove_files', []);
        if (!empty($toRemove)) {
            foreach ($toRemove as $path) {
                if (in_array($path, $existing, true)) {
                    Storage::disk(self::SECURE_DISK)->delete($path);
                }
            }
            $existing = array_values(array_diff($existing, $toRemove));
        }

        // 2) Store any newly uploaded files on the encrypted disk.
        $newPaths = $this->storeMediaFiles($request);

        $ldms->update([
            'message_content' => $request->input('message_content'),
            'media_type'      => $request->input('media_type', $ldms->media_type),
            'media_file_path' => array_values(array_merge($existing, $newPaths)) ?: null,
        ]);

        ActivityLog::record('student', $ldms->student_id, 'ldms_updated',
            "Updated LDMS #{$ldms->ldms_id}");

        return redirect()
            ->route('student.ldms.index')
            ->with('status', 'Your message has been updated.');
    }

    public function destroy(Ldms $ldms)
    {
        $this->authorizeStudent($ldms);

        if ($ldms->is_released) {
            return back()->withErrors(['ldms' => 'Released messages cannot be deleted.']);
        }

        foreach ((array) ($ldms->media_file_path ?? []) as $path) {
            Storage::disk(self::SECURE_DISK)->delete($path);
        }
        $ldms->delete();

        ActivityLog::record('student', (string) Auth::guard('student')->id(), 'ldms_deleted',
            "Deleted LDMS #{$ldms->ldms_id}");

        return redirect()
            ->route('student.ldms.index')
            ->with('status', 'Message removed.');
    }

    // -----------------------------------------------------------
    // ADMIN — list + view
    // -----------------------------------------------------------

    /**
     * List ALL students' LDMS messages. Includes search, filter, and
     * media-type chips. Mirrors crisis index for consistency.
     */
    public function adminIndex(Request $request)
    {
        $tab = in_array($request->query('tab'), ['pending', 'released'], true)
            ? $request->query('tab')
            : 'pending';

        $applyFilters = function ($query) use ($request) {
            if ($s = trim((string) $request->query('search'))) {
                $query->where(function ($w) use ($s) {
                    $w->where('ldms.ldms_id', 'like', "%{$s}%")
                      ->orWhere('ldms.student_id', 'like', "%{$s}%")
                      ->orWhereHas('student', function ($sq) use ($s) {
                          $sq->where('student_id', 'like', "%{$s}%");
                      });
                });
            }

            if ($media = $request->query('media_type')) {
                $query->where('media_type', $media);
            }

            // Filter by student's life status — quick way to find
            // releasable messages (student deceased + message pending).
            if ($request->query('student_status') === 'deceased') {
                $query->whereHas('student', fn($q) => $q->where('status', 'deceased'));
            } elseif ($request->query('student_status') === 'active') {
                $query->whereHas('student', fn($q) => $q->where('status', 'active'));
            }

            [$from, $to] = $this->resolveDateRange($request);
            if ($from) $query->whereDate('ldms.updated_at', '>=', $from);
            if ($to)   $query->whereDate('ldms.updated_at', '<=', $to);

            return $query;
        };

        $pending = $applyFilters(
            Ldms::with(['student'])
                ->where('is_released', false)
                ->orderByDesc('updated_at')
        )->paginate(15, ['*'], 'pending')->withQueryString();

        $released = $applyFilters(
            Ldms::with(['student'])
                ->where('is_released', true)
                ->orderByDesc('date_triggered')
        )->paginate(15, ['*'], 'released')->withQueryString();

        // Media-type breakdown (for chip row), scoped to active tab.
        $mediaTotals = Ldms::query()
            ->where('is_released', $tab === 'released')
            ->selectRaw('media_type, COUNT(*) as total')
            ->groupBy('media_type')
            ->pluck('total', 'media_type')
            ->toArray();

        return view('admin.ldms.index', compact(
            'tab', 'pending', 'released', 'mediaTotals'
        ));
    }

    /**
     * Show one LDMS to an admin.
     *
     * IMPORTANT — privacy design (FYP scope):
     * The view DOES NOT display the message body or attachment filenames.
     * The encrypted contents are intended ONLY for the next of kin. The
     * admin's role is to verify prerequisites (student deceased, NoK
     * exists) and click "Release" — they never need to read the content.
     *
     * The controller still loads the model so we can show metadata
     * (media type, attachment count, timestamps). The Blade template
     * intentionally never renders $ldms->message_content.
     */
    public function adminShow(Ldms $ldms)
    {
        $ldms->load('student', 'confirmation', 'nextOfKin');

        $recipients = NextOfKin::where('student_id', $ldms->student_id)->get();
        $studentDeceased = ($ldms->student?->status ?? null) === 'deceased';

        // Lecturer context (same shape as crisis/death show, so the right
        // sidebar can render an identical "Student's Lecturers" panel).
        $studentCourses = DB::table('student_courses')
            ->leftJoin('lecturers', 'student_courses.lecturer_id', '=', 'lecturers.lecturer_id')
            ->where('student_courses.student_id', $ldms->student_id)
            ->orderBy('student_courses.course_code')
            ->select(
                'student_courses.course_code',
                'student_courses.course_name',
                'student_courses.lecturer_name_raw',
                'lecturers.lecturer_id',
                'lecturers.first_name',
                'lecturers.last_name',
                'lecturers.email',
            )
            ->get();

        return view('admin.ldms.show', compact(
            'ldms', 'recipients', 'studentDeceased', 'studentCourses'
        ));
    }

    // -----------------------------------------------------------
    // ADMIN — trigger release
    // -----------------------------------------------------------

    public function trigger(Ldms $ldms)
    {
        /** @var \App\Models\Admin $admin */
        $admin = Auth::guard('admin')->user();

        if (!$admin->active) abort(403, 'Inactive admin account.');
        $perms = (array) ($admin->permissions ?? []);
        if (!in_array('trigger_ldms', $perms, true) && $admin->role !== 'super_admin') {
            abort(403, 'You do not have permission to trigger LDMS releases.');
        }

        if ($ldms->is_released) {
            return back()->withErrors(['ldms' => 'This message has already been released.']);
        }

        $payload = [
            'ldms_id'         => $ldms->ldms_id,
            'student_id'      => $ldms->student_id,
            'nok_id'          => $ldms->nok_id,
            'confirmation_id' => $ldms->confirmation_id,
            'media_type'      => $ldms->media_type,
            'triggered_by'    => $admin->admin_id,
            'triggered_at'    => now()->toIso8601String(),
        ];

        $result = $this->blockchain->recordEvent(
            'LDMS_TRIGGERED',
            $payload,
            $ldms->ldms_id,
            'ldms'
        );

        $ldms->update([
            'is_released'    => true,
            'date_triggered' => now(),
        ]);

        ActivityLog::record('admin', (string) $admin->admin_id, 'ldms_triggered',
            "Released LDMS #{$ldms->ldms_id} (hash: " . substr($result['hash'], 0, 16) . '…)');

        $noks = NextOfKin::where('student_id', $ldms->student_id)->get();
        foreach ($noks as $nok) {
            $this->notifications->send(
                recipientType: 'nok',
                recipientId:   (string) $nok->nok_id,
                email:         $nok->email,
                mailable:      new LDMSReleasedMail($ldms, $nok->first_name),
                notificationType: 'ldms_released',
                subject:       'A message has been released for you',
                message:       'A message left by the student has been securely released to you. Please log in to view.',
                link:          route('nok.ldms.show', $ldms->ldms_id),
                studentId:     $ldms->student_id,
            );
        }

        return back()->with('status', 'Message has been released to the next of kin.');
    }

    // -----------------------------------------------------------
    // NOK — view a released message
    // -----------------------------------------------------------

    public function nokShow(Ldms $ldms)
    {
        /** @var \App\Models\NextOfKin $nok */
        $nok = Auth::guard('nok')->user();

        if (!$ldms->is_released) abort(403, 'This message has not been released.');
        if ($ldms->student_id !== $nok->student_id) {
            abort(403, 'You are not authorised to view this message.');
        }

        $ldms->load('student', 'confirmation');

        ActivityLog::record('nok', (string) $nok->nok_id, 'ldms_viewed',
            "NOK viewed LDMS #{$ldms->ldms_id}");

        return view('nok.ldms.show', compact('ldms'));
    }

    /**
     * Stream a single LDMS attachment to a verified NOK.
     *
     * Because files live on the encrypted disk we cannot just expose
     * a public URL — we must read & decrypt through the Storage facade
     * and re-stream the bytes, with strict authorization first.
     */
    public function nokDownload(Ldms $ldms, string $filename)
    {
        /** @var \App\Models\NextOfKin $nok */
        $nok = Auth::guard('nok')->user();

        if (!$ldms->is_released) abort(403);
        if ($ldms->student_id !== $nok->student_id) abort(403);

        // Find the matching path in the manifest — never trust the URL alone.
        $paths = (array) ($ldms->media_file_path ?? []);
        $match = collect($paths)->first(fn($p) => basename($p) === $filename);
        if (!$match) abort(404);

        $disk = Storage::disk(self::SECURE_DISK);
        if (!$disk->exists($match)) abort(404);

        ActivityLog::record('nok', (string) $nok->nok_id, 'ldms_file_downloaded',
            "NOK downloaded {$filename} from LDMS #{$ldms->ldms_id}");

        // Stream decrypted bytes; the disk handles decryption transparently.
        return response()->streamDownload(
            function () use ($disk, $match) {
                echo $disk->get($match);
            },
            $filename
        );
    }

    // -----------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------

    protected function authorizeStudent(Ldms $ldms): void
    {
        /** @var \App\Models\Student $student */
        $student = Auth::guard('student')->user();
        if (!$student || $ldms->student_id !== $student->student_id) {
            abort(403);
        }
    }

    /**
     * Persist any uploaded files onto the encrypted LDMS disk.
     *
     * We give every file a random name so the original filename never
     * leaks into the filesystem (which an attacker with disk access
     * could otherwise still read, even with ciphertext contents).
     * The mime/extension is preserved so downloads still work.
     */
    protected function storeMediaFiles($request): array
    {
        $paths = [];
        if (!$request->hasFile('media_files')) {
            return $paths;
        }

        $disk = Storage::disk(self::SECURE_DISK);

        foreach ($request->file('media_files') as $file) {
            if (!$file || !$file->isValid()) continue;

            $ext      = strtolower($file->getClientOriginalExtension() ?: 'bin');
            $safeName = Str::random(40) . '.' . $ext;
            $path     = 'ldms-media/' . $safeName;

            // putFileAs() runs the bytes through the encrypted adapter.
            $disk->putFileAs('ldms-media', $file, $safeName);

            $paths[] = $path;
        }

        return $paths;
    }

    /**
     * Resolve the date_range select into a [from, to] Carbon pair.
     */
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
