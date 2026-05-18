<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Mail\OtpMail;
use App\Models\NextOfKin;
use App\Services\ImaalumScraperService;
use App\Services\SmsDemoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

/**
 * AuthController
 *
 * Handles login for four roles:
 *   - STUDENT   → authenticates via iMaalum (api.quddus.my).
 *   - ADMIN     → traditional Laravel auth against the admins table.
 *   - NOK       → passwordless: kin enters their registered email, picks
 *                 Email or SMS delivery, receives a 4-digit OTP, enters it
 *                 to complete login. SMS is delivered via SmsDemoService
 *                 (writes to storage/logs/sms.log) for FYP scope; swap for
 *                 a real provider in production.
 *   - LECTURER  → not exposed in the UI yet.
 */
class AuthController extends Controller
{
    // Session keys for the pending OTP between "Send Code" and "Verify".
    private const OTP_HASH_KEY = 'nok_pending_otp_hash';
    private const OTP_NOK_ID   = 'nok_pending_nok_id';
    private const OTP_EXPIRES  = 'nok_pending_otp_expires_at';
    private const OTP_CHANNEL  = 'nok_pending_otp_channel';
    private const OTP_CONTACT  = 'nok_pending_otp_contact';

    private const OTP_TTL_MINUTES = 5;
    private const OTP_LENGTH      = 4;

    public function __construct(
        protected ImaalumScraperService $imaalum,
        protected SmsDemoService $sms,
    ) {}

    // ==================================================================
    // LOGIN FORM
    // ==================================================================

    public function showLogin(Request $request)
    {
        if (Auth::guard('student')->check()) return redirect()->route('student.dashboard');
        if (Auth::guard('admin')->check())   return redirect()->route('admin.dashboard');
        if (Auth::guard('nok')->check())     return redirect()->route('nok.dashboard');

        return view('auth.login');
    }

    public function login(LoginRequest $request)
    {
        $role       = $request->input('role', 'student');
        $identifier = $request->input('identifier');
        $password   = $request->input('password');
        $remember   = (bool) $request->input('remember', false);

        // Throttle: 5 attempts per minute per IP+identifier
        $key = 'login:' . strtolower($identifier ?? '') . '|' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $secs = RateLimiter::availableIn($key);
            return back()->withErrors([
                'auth' => "Too many login attempts. Try again in {$secs} seconds.",
            ])->withInput($request->only('identifier', 'role'));
        }

        $result = match ($role) {
            'student' => $this->loginAsStudent($identifier, $password, $remember),
            'admin'   => $this->loginAsAdmin($identifier, $password, $remember),
            'nok'     => $this->loginAsKin($identifier, $request->input('delivery', 'email')),
            default   => ['ok' => false, 'reason' => 'This login type is not available yet.'],
        };

        if (!$result['ok']) {
            RateLimiter::hit($key, 60);
            return back()->withErrors(['auth' => $result['reason']])
                         ->withInput($request->only('identifier', 'role', 'delivery'));
        }

        RateLimiter::clear($key);
        $request->session()->regenerate();

        return redirect()->intended($result['redirect']);
    }

    // ==================================================================
    // STUDENT
    // ==================================================================

    protected function loginAsStudent(string $matric, string $password, bool $remember): array
    {
        $sync = $this->imaalum->syncStudent($matric, $password);

        if (!$sync['success']) {
            return [
                'ok'     => false,
                'reason' => $sync['reason'] ?? 'Please check your matric ID and IIUM password.',
            ];
        }

        $student = $sync['student'];
        if (!$student) {
            return ['ok' => false, 'reason' => 'iMaalum returned no student record. Please try again.'];
        }

        Auth::guard('student')->login($student, $remember);

        $redirect = $student->needs_email_confirmation
            ? route('student.confirm-email.show')
            : route('student.dashboard');

        return ['ok' => true, 'redirect' => $redirect];
    }

    // ==================================================================
    // ADMIN
    // ==================================================================

    protected function loginAsAdmin(string $identifier, string $password, bool $remember): array
    {
        $ok = Auth::guard('admin')->attempt(
            ['email' => $identifier, 'password' => $password],
            $remember
        );

        if (!$ok) {
            return ['ok' => false, 'reason' => 'Invalid admin email or password.'];
        }

        $admin = Auth::guard('admin')->user();
        if (!$admin->active) {
            Auth::guard('admin')->logout();
            return ['ok' => false, 'reason' => 'This admin account is deactivated.'];
        }

        return ['ok' => true, 'redirect' => route('admin.dashboard')];
    }

    // ==================================================================
    // NEXT OF KIN — OTP FLOW
    // ==================================================================

    /**
     * Step 1: kin submits email + delivery choice on the login form.
     * We generate a 4-digit OTP, store its hash in the session, and
     * dispatch it via the requested channel.
     */
    protected function loginAsKin(string $email, string $delivery): array
    {
        $kin = NextOfKin::where('email', $email)->first();
        if (!$kin) {
            // Same error for unknown email vs. found email — don't leak which
            // emails are registered.
            return ['ok' => false, 'reason' => 'We could not start a verification for that email.'];
        }

        $channel = in_array($delivery, ['email', 'sms'], true) ? $delivery : 'email';

        // SMS requested but no phone on file? Fall back to email rather
        // than failing — better UX than a hard error.
        if ($channel === 'sms' && empty($kin->phone)) {
            $channel = 'email';
        }

        $code = $this->generateOtp();

        // Stash the OTP HASH (not the raw code) in the session along with
        // expiry, the kin's id, the channel, and the contact (for the UI).
        session([
            self::OTP_HASH_KEY => Hash::make($code),
            self::OTP_NOK_ID   => $kin->nok_id,
            self::OTP_EXPIRES  => now()->addMinutes(self::OTP_TTL_MINUTES)->toIso8601String(),
            self::OTP_CHANNEL  => $channel,
            self::OTP_CONTACT  => $channel === 'email' ? $kin->email : $kin->phone,
        ]);

        $this->dispatchOtp($kin, $code, $channel);

        // Redirect to the OTP entry page.
        return ['ok' => true, 'redirect' => route('nok.twofactor.show')];
    }

    /**
     * Step 2: render the OTP entry page.
     */
    public function showTwoFactor()
    {
        if (!session(self::OTP_HASH_KEY) || !session(self::OTP_NOK_ID)) {
            return redirect()->route('login')->withErrors([
                'auth' => 'No pending verification. Please sign in again.',
            ]);
        }

        return view('auth.twofactor', [
            'channel' => session(self::OTP_CHANNEL, 'email'),
            'contact' => $this->maskContact(
                session(self::OTP_CONTACT, ''),
                session(self::OTP_CHANNEL, 'email')
            ),
        ]);
    }

    /**
     * Step 3: kin submits the 4-digit code. Verify and complete login.
     */
    public function verifyTwoFactor(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:' . self::OTP_LENGTH,
        ]);

        // Rate-limit verification attempts: 5 per minute per IP.
        $key = 'nok-otp-verify:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $secs = RateLimiter::availableIn($key);
            return back()->withErrors([
                'code' => "Too many attempts. Try again in {$secs} seconds.",
            ]);
        }

        $hash      = session(self::OTP_HASH_KEY);
        $nokId     = session(self::OTP_NOK_ID);
        $expiresAt = session(self::OTP_EXPIRES);

        if (!$hash || !$nokId) {
            return redirect()->route('login')->withErrors([
                'auth' => 'No pending verification. Please sign in again.',
            ]);
        }

        if ($expiresAt && now()->greaterThan(\Carbon\Carbon::parse($expiresAt))) {
            $this->clearOtpSession();
            return redirect()->route('login')->withErrors([
                'auth' => 'Your code expired. Please sign in again.',
            ]);
        }

        if (!Hash::check($request->input('code'), $hash)) {
            RateLimiter::hit($key, 60);
            return back()->withErrors(['code' => 'That code is incorrect.']);
        }

        //
        $kin = NextOfKin::find($nokId);
        if (!$kin) {
            $this->clearOtpSession();
            return redirect()->route('login')->withErrors([
                'auth' => 'Your account could not be found.',
            ]);
        }

        Auth::guard('nok')->login($kin);
        $request->session()->regenerate();

        // The TwoFactorMiddleware checks this; setting it grants access
        // to the protected NOK routes for the next 30 minutes.
        session(['nok_2fa_verified_at' => now()]);

        $this->clearOtpSession();
        RateLimiter::clear($key);

        return redirect()->route('nok.dashboard');
    }

    /**
     * Step 2b (optional): user pressed "Resend code".
     */
    public function resendOtp(Request $request)
    {
        $nokId = session(self::OTP_NOK_ID);
        if (!$nokId) {
            return redirect()->route('login')->withErrors([
                'auth' => 'No pending verification. Please sign in again.',
            ]);
        }

        // Throttle resends: 3 per 5 minutes per IP.
        $key = 'nok-otp-resend:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 3)) {
            $secs = RateLimiter::availableIn($key);
            return back()->withErrors([
                'code' => "Please wait {$secs} seconds before requesting another code.",
            ]);
        }
        RateLimiter::hit($key, 300);

        $kin = NextOfKin::find($nokId);
        if (!$kin) {
            $this->clearOtpSession();
            return redirect()->route('login')->withErrors([
                'auth' => 'Your account could not be found.',
            ]);
        }

        $channel = session(self::OTP_CHANNEL, 'email');
        $code = $this->generateOtp();

        session([
            self::OTP_HASH_KEY => Hash::make($code),
            self::OTP_EXPIRES  => now()->addMinutes(self::OTP_TTL_MINUTES)->toIso8601String(),
        ]);

        $this->dispatchOtp($kin, $code, $channel);

        return back()->with('status', 'A new code has been sent.');
    }

    // ==================================================================
    // LOGOUT (all guards)
    // ==================================================================

    public function logout(Request $request)
    {
        foreach (['student', 'admin', 'nok', 'lecturer'] as $g) {
            if (Auth::guard($g)->check()) {
                Auth::guard($g)->logout();
            }
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'You have been logged out.');
    }

    // ==================================================================
    // HELPERS
    // ==================================================================

    /** Generate a zero-padded N-digit numeric OTP. */
    private function generateOtp(): string
    {
        $max = (int) str_repeat('9', self::OTP_LENGTH);
        return str_pad((string) random_int(0, $max), self::OTP_LENGTH, '0', STR_PAD_LEFT);
    }

    /** Send the OTP via the chosen channel. */
    private function dispatchOtp(NextOfKin $kin, string $code, string $channel): void
    {
        if ($channel === 'sms') {
            $body = "e-Tawassul: your verification code is {$code}. "
                  . "It expires in " . self::OTP_TTL_MINUTES . " minutes.";
            $this->sms->send($kin->phone, $body);

            // Demo only: flash the code so the kin can see it on the next
            // page (no real SMS provider is wired up). REMOVE this line
            // when integrating a real SMS provider in production.
            session()->flash('demo_otp', $code);
            return;
        }

        // Default channel: email via SMTP (Mailpit in local).
        Mail::to($kin->email)->queue(new OtpMail($code, $kin->full_name));
    }

    /** Mask an email or phone for display on the OTP page. */
    private function maskContact(string $contact, string $channel): string
    {
        if ($contact === '') return '';

        if ($channel === 'sms') {
            // Show last 3 digits.
            $tail = substr($contact, -3);
            return str_repeat('*', max(0, strlen($contact) - 3)) . $tail;
        }

        // Email: a***b@domain.tld
        if (!str_contains($contact, '@')) return $contact;
        [$local, $domain] = explode('@', $contact, 2);
        $first = substr($local, 0, 1);
        $last  = substr($local, -1);
        $masked = strlen($local) <= 2 ? $first . '*' : $first . str_repeat('*', max(1, strlen($local) - 2)) . $last;
        return $masked . '@' . $domain;
    }

    private function clearOtpSession(): void
    {
        session()->forget([
            self::OTP_HASH_KEY, self::OTP_NOK_ID, self::OTP_EXPIRES,
            self::OTP_CHANNEL, self::OTP_CONTACT,
        ]);
    }
}
