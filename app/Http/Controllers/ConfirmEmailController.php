<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * ConfirmEmailController
 *
 * One-time gate shown to a newly-synced student on first login.
 * iMaalum's API doesn't return the student's email, so we auto-guess
 * `firstnamelastname.lastpart@student.iium.edu.my` during the sync and
 * ask the student to confirm or correct it before letting them into the
 * dashboard.
 *
 * After the student confirms:
 *   - students.email is updated to whatever they typed
 *   - students.needs_email_confirmation = false (forever)
 *   - they're redirected to the dashboard
 */
class ConfirmEmailController extends Controller
{
    /**
     * GET /student/confirm-email — show the form.
     */
    public function show()
    {
        /** @var \App\Models\Student|null $student */
        $student = Auth::guard('student')->user();

        if (!$student) {
            return redirect()->route('login');
        }

        // If they already confirmed, no need to show this again.
        if (!$student->needs_email_confirmation) {
            return redirect()->route('student.dashboard');
        }

        return view('auth.confirm-email', ['student' => $student]);
    }

    /**
     * POST /student/confirm-email — save the confirmed email.
     */
    public function store(Request $request)
    {
        /** @var \App\Models\Student|null $authUser */
        $authUser = Auth::guard('student')->user();

        if (!$authUser) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'email' => [
                'required',
                'email:rfc',
                'max:255',
                Rule::unique('students', 'email')
                    ->ignore($authUser->getKey(), 'student_id'),
            ],
        ], [
            'email.unique' => 'That email is already used by another student. Please use a different one.',
        ]);

        // Use the model directly (not the auth user proxy) so the
        // ->save() call is unambiguous to the IDE / static analysers.
        $student = Student::find($authUser->getKey());

        if (!$student) {
            return redirect()->route('login')->withErrors([
                'auth' => 'Your session expired. Please log in again.',
            ]);
        }

        $student->email = $validated['email'];
        $student->needs_email_confirmation = false;
        $student->save();

        return redirect()
            ->route('student.dashboard')
            ->with('status', "Email confirmed. You're all set!");
    }
}
