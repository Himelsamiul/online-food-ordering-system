<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FrontendAuth
{
    public function handle(Request $request, Closure $next)
    {
        if (! Auth::guard('frontend')->check()) {
            return redirect()->route('login')
                ->with('error', 'Please login first');
        }

        // An admin can switch an account off while its owner is mid-session;
        // without this they would keep browsing until the cookie expired.
        if (! Auth::guard('frontend')->user()->isActive()) {
            $email = Auth::guard('frontend')->user()->email;

            Auth::guard('frontend')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->with('error', 'Your account has been deactivated. Ask an admin to switch it back on.')
                ->with('offer_activation_help', true)
                ->with('help_email', $email);
        }

        return $next($request);
    }
}
