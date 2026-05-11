<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!Auth::guard($role)->check()) {
            return redirect()->route('login')
                ->withErrors(['auth' => 'Please log in as '.ucfirst($role).' to continue.']);
        }

        // share with views & request context
        $request->attributes->set('auth_role', $role);
        $request->attributes->set('auth_user', Auth::guard($role)->user());

        view()->share('authRole', $role);
        view()->share('authUser', Auth::guard($role)->user());

        return $next($request);
    }
}
