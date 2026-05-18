<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * EnsureEmailConfirmed
 *
 * If the logged-in student still has needs_email_confirmation = true,
 * redirect them to the confirm-email page. Otherwise let the request through.
 *
 * Applied to student-only routes (NOT the confirm-email page itself,
 * obviously, or we'd loop forever).
 */
class EnsureEmailConfirmed
{
    public function handle(Request $request, Closure $next)
    {
        $student = Auth::guard('student')->user();

        if ($student && $student->needs_email_confirmation) {
            return redirect()->route('student.confirm-email.show');
        }

        return $next($request);
    }
}
