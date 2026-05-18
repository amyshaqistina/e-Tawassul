<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\NextOfKin;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * AdminStudentController
 *
 * Admin-side view of a single student record, plus the emergency
 * fallback for adding/managing Next of Kin when the student didn't
 * pre-fill any.
 *
 * Use case: a student dies without ever having added kin to the
 * system. Family contacts the university; admin gathers kin info
 * offline (death certificate, phone calls); admin enters the kin
 * here so they can be issued a login OTP and proceed with the
 * death-confirmation / LDMS flow.
 *
 * Every admin action is logged with the admin's ID for audit.
 */
class AdminStudentController extends Controller
{
    public function show(Student $student)
    {
        $student->load('nextOfKin');

        return view('admin.students.show', compact('student'));
    }

    public function storeKin(Request $request, Student $student)
    {
        /** @var \App\Models\Admin $admin */
        $admin = Auth::guard('admin')->user();

        // Only admins with verify_death permission (or super_admin) may
        // register kin on a student's behalf — this is an emergency
        // power and should be tightly scoped.
        $perms = (array) ($admin->permissions ?? []);
        if (!in_array('verify_death', $perms, true) && $admin->role !== 'super_admin') {
            abort(403, 'You do not have permission to register next of kin on behalf of students.');
        }

        $validated = $request->validate([
            'first_name'              => ['required', 'string', 'max:100'],
            'last_name'               => ['required', 'string', 'max:100'],
            'relationship_to_student' => ['required', 'string', 'max:50'],
            'email'                   => ['required', 'email', 'max:191', Rule::unique('next_of_kin', 'email')],
            'phone'                   => ['required', 'string', 'max:20'],
            'address'                 => ['nullable', 'string', 'max:500'],
            'admin_note'              => ['nullable', 'string', 'max:500'],
        ], [
            'email.unique' => 'This email is already registered as a next of kin. Cannot create a duplicate record.',
        ]);

        $existingCount = NextOfKin::where('student_id', $student->student_id)->count();
        $makePrimary = $existingCount === 0; // first kin auto-primary

        if ($makePrimary) {
            NextOfKin::where('student_id', $student->student_id)
                ->update(['is_primary' => false]);
        }

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
            'emergency_contact_verified' => true, // admin attests
            'password'                   => Hash::make($stubPassword),
            'registered_by'              => 'admin',
            'registered_at'              => now(),
        ]);

        $note = $validated['admin_note'] ?? null;
        $auditDetail = "Admin registered NoK #{$nok->nok_id} ({$nok->full_name}) for student {$student->student_id}";
        if ($note) $auditDetail .= ". Note: {$note}";

        ActivityLog::record('admin', (string) $admin->admin_id, 'kin_added_by_admin',
            $auditDetail);

        return redirect()
            ->route('admin.students.show', $student->student_id)
            ->with('status', "Next of kin '{$nok->full_name}' registered. They can now log in at /login via the Next of Kin tab using {$nok->email}.");
    }
}
