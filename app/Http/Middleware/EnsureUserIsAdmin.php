<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Runs after `auth`: the account must still be active and hold the
 * admin.access permission. Deactivated users are logged out immediately,
 * even with a live session or remember-me cookie.
 */
class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->is_active) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('admin.login')->withErrors(['email' => __('auth.inactive')]);
        }

        if (! $user->can('admin.access')) {
            abort(403);
        }

        return $next($request);
    }
}
