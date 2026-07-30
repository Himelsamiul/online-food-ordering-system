<?php

namespace App\Http\Middleware;

use App\Support\AuditLogger;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Guards the rider portal.
 *
 * Repeats the active check on every request, not just at login: an admin can
 * switch a rider off mid-shift, and without this they would keep marking
 * orders delivered until their cookie expired.
 */
class RiderAuth
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::guard('rider')->check()) {
            // By route name, not back() — back() resolves off the Referer and
            // silently falls through to "/" when it is absent.
            return redirect()->route('rider.login');
        }

        $rider = Auth::guard('rider')->user();

        if (!$rider->canSignIn()) {
            $name = $rider->name;

            AuditLogger::auth(
                AuditLogger::ACTION_LOGOUT,
                "Rider session ended for {$name} — the account was switched off",
                $rider,
            );

            Auth::guard('rider')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('rider.login')
                ->with('error', 'Your rider account has been switched off. Contact the office.');
        }

        return $next($request);
    }
}
