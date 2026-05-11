<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Jobs\ScrapeImaalumData;
use App\Mail\OtpMail;
use App\Models\ActivityLog;
use App\Models\Admin;
use App\Models\Lecturer;
use App\Models\NextOfKin;
use App\Models\Student;
use App\Services\TwoFactorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request, TwoFactorService $two)
    {
        $key = 'login:'.Str::lower($request->ip()).':'.$request->input('identifier');
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return back()->withErrors(['auth' => 'Too many attempts. Please try again in a minute.'])->withInput();
        }
        RateLimiter::hit($key, 60);

        $role       = $request->input('role');
        $identifier = $request->input('identifier');
        $password   = $request->input('password');
        $remember   = (bool) $request->input('remember', false);

        $user = null;
        switch ($role) {
            case 'student':
                $user = Student::where('student_id', $identifier)->orWhere('email', $identifier)->first();
                break;
            case 'admin':
                $user = Admin::where('email', $identifier)->first();
                break;
            case 'lecturer':
                $user = Lecturer::where('email', $identifier)->first();
                break;
            case 'nok':
                $user = NextOfKin::where('email', $identifier)->orWhere('student_id', $identifier)->first();
                break;
        }

        if (!$user || !Hash::check($password, $user->password)) {
            return back()->withErrors(['auth' => 'Invalid credentials.'])->withInput();
        }

        RateLimiter::clear($key);

        // NOK requires 2FA — stash pending nok in session, send OTP
        if ($role === 'nok') {
            $code = $two->generate($user->email, 'login');
            session([
                'pending_nok_id'   => $user->nok_id,
                'pending_nok_remember' => $remember,
            ]);
            try {
                Mail::to($user->email)->send(new OtpMail($code, $user->first_name));
            } catch (\Throwable $e) {
                // Mail config may be off — show on screen for demo
                session()->flash('demo_otp', $code);
            }
            ActivityLog::record('nok', (string)$user->nok_id, 'login_otp_sent', 'OTP requested for NOK login');
            return redirect()->route('nok.twofactor.show');
        }

        Auth::guard($role)->login($user, $remember);
        ActivityLog::record($role, (string)$user->getKey(), 'login', ucfirst($role).' logged in');

        // Trigger iMaalum scrape for students — queued, never blocks
        if ($role === 'student') {
            ScrapeImaalumData::dispatch($user->student_id, $password);
        }

        return redirect()->route($role.'.dashboard');
    }

    public function showTwoFactor()
    {
        if (!session('pending_nok_id')) {
            return redirect()->route('login');
        }
        return view('auth.twofactor');
    }

    public function verifyTwoFactor(Request $request, TwoFactorService $two)
    {
        $request->validate(['code' => 'required|digits:6']);

        $nokId = session('pending_nok_id');
        $nok = NextOfKin::find($nokId);
        if (!$nok) return redirect()->route('login');

        if (!$two->verify($nok->email, $request->code, 'login')) {
            return back()->withErrors(['code' => 'Invalid or expired code.']);
        }

        Auth::guard('nok')->login($nok, (bool) session('pending_nok_remember'));
        session()->forget(['pending_nok_id', 'pending_nok_remember']);
        session(['nok_2fa_verified_at' => now()]);

        ActivityLog::record('nok', (string)$nok->nok_id, 'login_2fa_verified', '2FA verified, NOK logged in');

        return redirect()->route('nok.dashboard');
    }

    public function resendOtp(TwoFactorService $two)
    {
        $nokId = session('pending_nok_id');
        if (!$nokId) return redirect()->route('login');

        $nok = NextOfKin::find($nokId);
        $code = $two->generate($nok->email, 'login');
        try {
            Mail::to($nok->email)->send(new OtpMail($code, $nok->first_name));
        } catch (\Throwable $e) {
            session()->flash('demo_otp', $code);
        }

        return back()->with('status', 'A new code has been sent.');
    }

    public function logout(Request $request)
    {
        foreach (['student','admin','nok','lecturer'] as $g) {
            if (Auth::guard($g)->check()) {
                ActivityLog::record($g, (string)Auth::guard($g)->user()->getKey(), 'logout', ucfirst($g).' logged out');
                Auth::guard($g)->logout();
            }
        }
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
