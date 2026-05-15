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
use Illuminate\Support\Facades\Auth;
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
}
