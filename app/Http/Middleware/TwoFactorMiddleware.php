<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TwoFactorMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!session('nok_2fa_verified_at')) {
            return redirect()->route('nok.twofactor.show')
                ->withErrors(['twofactor' => 'Please complete 2FA verification to continue.']);
        }

        // Re-prompt every 30 minutes for sensitive LDMS access
        $verifiedAt = session('nok_2fa_verified_at');
        if (now()->diffInMinutes($verifiedAt) > 30) {
            session()->forget('nok_2fa_verified_at');
            return redirect()->route('nok.twofactor.show')
                ->withErrors(['twofactor' => '2FA session expired. Please verify again.']);
        }

        return $next($request);
    }
}
