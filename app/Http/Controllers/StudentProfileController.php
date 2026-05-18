<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\NextOfKin;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * StudentProfileController
 *
 * Handles the student-editable portions of their profile and the
 * student's management of their own Next of Kin records.
 *
 * Identity-level fields (student_id, full_name, kulliyyah, email) come
 * from iMaalum and are NOT editable here.
 *
 * Student-editable fields:
 *   - phone, emergency_contact   (contact info)
 *   - mahallah                   (residence)
 *   - programme, year_of_study   (academic, sometimes blank from iMaalum)
 *
 * Next of Kin: full create / update / delete / set-primary, all scoped
 * to the logged-in student's own kin records.
 */
class StudentProfileController extends Controller
{
    // ==================================================================
    // PROFILE FIELDS
    // ==================================================================

    public function updateProfile(Request $request)
    {
        /** @var Student $student */
        $student = Auth::guard('student')->user();

        $validated = $request->validate([
            'phone'             => ['nullable', 'string', 'max:20'],
            'emergency_contact' => ['nullable', 'string', 'max:20'],
            'mahallah'          => ['nullable', 'string', 'max:100'],
            'programme'         => ['nullable', 'string', 'max:150'],
            'year_of_study'     => ['nullable', 'string', 'max:10'],
        ]);

        $student->update($validated);

        ActivityLog::record('student', $student->student_id, 'profile_updated',
            'Updated editable profile fields');

        return back()->with('status', 'Profile saved.');
    }

    // ==================================================================
    // NEXT OF KIN — CRUD
    // ==================================================================

    public function storeKin(Request $request)
    {
        /** @var Student $student */
        $student = Auth::guard('student')->user();

        $validated = $request->validate([
            'first_name'              => ['required', 'string', 'max:100'],
            'last_name'               => ['required', 'string', 'max:100'],
            'relationship_to_student' => ['required', 'string', 'max:50'],
            'email'                   => ['required', 'email', 'max:191', Rule::unique('next_of_kin', 'email')],
            'phone'                   => ['required', 'string', 'max:20'],
            'address'                 => ['nullable', 'string', 'max:500'],
            'is_primary'              => ['nullable', 'boolean'],
        ], [
            'email.unique' => 'This email is already registered as a next of kin in our system. Please use a different email for this contact.',
        ]);

        // First kin a student adds is automatically marked primary.
        $existingCount = NextOfKin::where('student_id', $student->student_id)->count();
        $makePrimary = (bool) ($validated['is_primary'] ?? false) || $existingCount === 0;

        if ($makePrimary) {
            NextOfKin::where('student_id', $student->student_id)
                ->update(['is_primary' => false]);
        }

        // NoK login is OTP-based (see AuthController::loginAsKin). They
        // don't need a real password, but the DB column is NOT NULL, so
        // we set an unguessable random stub.
        $stubPassword = Str::random(60);

        $nok = NextOfKin::create([
            'student_id'                 => $student->student_id,
            'first_name'                 => $validated['first_name'],
            'last_name'                  => $validated['last_name'],
            'relationship_to_student'    => $validated['relationship_to_student'],
            'email'                      => $validated['email'],
            'phone'                      => $validated['phone'],
            'address'                    => $validated['address'] ?? null,
            'is_primary'                 => $makePrimary,
            'emergency_contact_verified' => false,
            'password'                   => Hash::make($stubPassword),
            'registered_by'              => 'student',
            'registered_at'              => now(),
        ]);

        ActivityLog::record('student', $student->student_id, 'kin_added',
            "Added next of kin #{$nok->nok_id} ({$nok->full_name})");

        return back()->with('status', "Next of kin '{$nok->full_name}' added.");
    }

    public function updateKin(Request $request, NextOfKin $kin)
    {
        /** @var Student $student */
        $student = Auth::guard('student')->user();

        if ($kin->student_id !== $student->student_id) {
            abort(403, 'You can only edit your own next of kin records.');
        }

        // If the kin has already submitted a death confirmation, lock
        // identity-bearing fields. Contact info edits remain allowed.
        $hasActed = $kin->deathConfirmations()->exists();

        $rules = [
            'phone'      => ['required', 'string', 'max:20'],
            'address'    => ['nullable', 'string', 'max:500'],
            'is_primary' => ['nullable', 'boolean'],
        ];

        if (!$hasActed) {
            $rules['first_name']              = ['required', 'string', 'max:100'];
            $rules['last_name']               = ['required', 'string', 'max:100'];
            $rules['relationship_to_student'] = ['required', 'string', 'max:50'];
            $rules['email']                   = ['required', 'email', 'max:191',
                                                  Rule::unique('next_of_kin', 'email')->ignore($kin->nok_id, 'nok_id')];
        }

        $validated = $request->validate($rules);

        if (!empty($validated['is_primary'])) {
            NextOfKin::where('student_id', $student->student_id)
                ->where('nok_id', '!=', $kin->nok_id)
                ->update(['is_primary' => false]);
        }

        $kin->fill($validated);
        $kin->save();

        ActivityLog::record('student', $student->student_id, 'kin_updated',
            "Updated next of kin #{$kin->nok_id} ({$kin->full_name})");

        return back()->with('status', "Next of kin '{$kin->full_name}' updated.");
    }

    public function destroyKin(NextOfKin $kin)
    {
        /** @var Student $student */
        $student = Auth::guard('student')->user();

        if ($kin->student_id !== $student->student_id) {
            abort(403, 'You can only delete your own next of kin records.');
        }

        if ($kin->deathConfirmations()->exists()) {
            return back()->withErrors([
                'kin' => "Cannot delete '{$kin->full_name}' because they've already submitted records in the system. Contact administration if removal is necessary.",
            ]);
        }

        $name = $kin->full_name;
        $wasPrimary = $kin->is_primary;
        $kin->delete();

        // If we just deleted the primary, promote the oldest remaining
        // kin to primary so the student always has a designated contact.
        if ($wasPrimary) {
            $next = NextOfKin::where('student_id', $student->student_id)
                ->orderBy('nok_id')
                ->first();
            if ($next) {
                $next->update(['is_primary' => true]);
            }
        }

        ActivityLog::record('student', $student->student_id, 'kin_deleted',
            "Removed next of kin ({$name})");

        return back()->with('status', "Next of kin '{$name}' removed.");
    }

    /**
     * Toggle which kin is primary. The student can change the primary
     * contact at any time. Exactly one kin is primary at a time.
     */
    public function makePrimaryKin(NextOfKin $kin)
    {
        /** @var Student $student */
        $student = Auth::guard('student')->user();

        if ($kin->student_id !== $student->student_id) {
            abort(403, 'You can only modify your own next of kin records.');
        }

        NextOfKin::where('student_id', $student->student_id)
            ->update(['is_primary' => false]);
        $kin->update(['is_primary' => true]);

        ActivityLog::record('student', $student->student_id, 'kin_primary_set',
            "Set #{$kin->nok_id} ({$kin->full_name}) as primary kin");

        return back()->with('status', "'{$kin->full_name}' is now your primary next of kin.");
    }

    // ==================================================================
    // DONATION RECEIVING — Bank info + DuitNow QR
    // ==================================================================
    //
    // These fields let a student receive donations directly from
    // members of the public who view their VERIFIED crisis case on the
    // public donate page. The fields are optional — a student who
    // doesn't fill them in still has their case shown publicly; donors
    // just won't see a "direct transfer" option.
    //
    // Privacy / safety design:
    //  - Account number is stored AES-encrypted at rest (see Student
    //    model's `encrypted` cast on `bank_account_number`).
    //  - The donor-facing donate page renders the account number with
    //    most digits masked, showing only the last 4. The full number
    //    is intentionally NOT exposed publicly; donors who want to
    //    transfer copy from a "Show full number" toggle that re-fetches
    //    from a separate (authed-or-CSRF-protected) endpoint. For FYP
    //    demo simplicity, we just render the masked + full form on the
    //    page (donors who view a verified case have legitimate intent).
    //  - The DuitNow QR is a public-disk image upload, capped at 2MB.

    public function updateBank(Request $request)
    {
        /** @var Student $student */
        $student = Auth::guard('student')->user();

        $validated = $request->validate([
            'bank_name'           => ['nullable', 'string', 'max:100'],
            'bank_account_holder' => ['nullable', 'string', 'max:150'],
            'bank_account_number' => ['nullable', 'string', 'max:30',
                                       'regex:/^[0-9 \-]+$/'],
        ], [
            'bank_account_number.regex' => 'Account number may only contain digits, spaces, and hyphens.',
        ]);

        // Normalise — strip whitespace/hyphens so the stored value is
        // pure digits; renderer adds spacing back for display.
        if (isset($validated['bank_account_number'])) {
            $validated['bank_account_number'] = preg_replace('/\D/', '', $validated['bank_account_number']) ?: null;
        }

        // If the student is clearing the bank fields completely, also
        // null them out individually so the encrypted cast doesn't
        // store an empty ciphertext.
        $student->fill([
            'bank_name'           => $validated['bank_name']           ?? null,
            'bank_account_holder' => $validated['bank_account_holder'] ?? null,
            'bank_account_number' => $validated['bank_account_number'] ?? null,
        ])->save();

        ActivityLog::record('student', $student->student_id, 'bank_info_updated',
            'Updated bank info' . ($validated['bank_account_number'] ?? null ? '' : ' (cleared)'));

        return back()->with('status', 'Donation bank details saved.');
    }

    public function uploadQr(Request $request)
    {
        /** @var Student $student */
        $student = Auth::guard('student')->user();

        $request->validate([
            'qr_code'  => ['required', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
        ], [
            'qr_code.image' => 'The QR code must be an image (PNG or JPG).',
            'qr_code.max'   => 'The QR code must be under 2 MB.',
        ]);

        // Remove any previous QR before saving the new one.
        if ($student->qr_code_path && Storage::disk('public')->exists($student->qr_code_path)) {
            Storage::disk('public')->delete($student->qr_code_path);
        }

        // Random filename so guessable URLs aren't possible.
        $ext  = strtolower($request->file('qr_code')->getClientOriginalExtension());
        $name = 'qr-' . $student->student_id . '-' . Str::random(12) . '.' . $ext;
        $path = $request->file('qr_code')->storeAs('qrcodes', $name, 'public');

        $student->update(['qr_code_path' => $path]);

        ActivityLog::record('student', $student->student_id, 'qr_uploaded',
            "Uploaded DuitNow QR ({$name})");

        return back()->with('status', 'DuitNow QR code uploaded.');
    }

    public function deleteQr()
    {
        /** @var Student $student */
        $student = Auth::guard('student')->user();

        if ($student->qr_code_path) {
            if (Storage::disk('public')->exists($student->qr_code_path)) {
                Storage::disk('public')->delete($student->qr_code_path);
            }
            $student->update(['qr_code_path' => null]);

            ActivityLog::record('student', $student->student_id, 'qr_deleted',
                'Removed DuitNow QR');
        }

        return back()->with('status', 'DuitNow QR code removed.');
    }
}
