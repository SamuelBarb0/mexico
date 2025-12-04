<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckTenantStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        if ($user->user_type === 'platform_admin') {
            return $next($request);
        }

        if (!$user->tenant) {
            Auth::logout();
            return redirect()->route('login')->withErrors([
                'email' => 'Your account is not associated with any tenant.'
            ]);
        }

        $tenant = $user->tenant;

        if ($tenant->isSuspended()) {
            Auth::logout();
            return redirect()->route('login')->withErrors([
                'email' => 'This tenant account has been suspended. Please contact support.'
            ]);
        }

        if (!$tenant->isActive()) {
            Auth::logout();
            return redirect()->route('login')->withErrors([
                'email' => 'This tenant account is not active.'
            ]);
        }

        if (!$user->is_active) {
            Auth::logout();
            return redirect()->route('login')->withErrors([
                'email' => 'Your user account has been deactivated.'
            ]);
        }

        return $next($request);
    }
}
