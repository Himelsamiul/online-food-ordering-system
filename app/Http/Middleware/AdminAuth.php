<?php

namespace App\Http\Middleware;

use App\Services\LoginHistoryRecorder;
use App\Support\AuditLogger;
use Closure;
use Illuminate\Http\Request;

class AdminAuth
{
    public function __construct(private readonly LoginHistoryRecorder $history)
    {
    }

    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) {
            return redirect()->route('admin.login');
        }

        $user = auth()->user();

        if (!$user->is_admin) {
            auth()->logout();

            return redirect()->route('admin.login')
                ->withErrors(['login' => 'You are not authorized as admin']);
        }

        /*
         * A superadmin can switch an account off while its owner is mid-session.
         * Without this they would keep working until the cookie expired, which
         * defeats the point of deactivating them.
         */
        if (!$user->isActive()) {
            $email  = $user->email;
            $reason = $user->deactivation_reason;

            AuditLogger::auth(
                AuditLogger::ACTION_LOGOUT,
                'Session ended for ' . $email . ' — the account was deactivated',
                $user,
            );

            $this->history->closeAdminSession($request, $user, 'deactivated');

            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('admin.login')
                ->with('error', 'Your account has been deactivated.' . ($reason ? ' Reason: ' . $reason : ''))
                ->with('offer_activation_help', true)
                ->with('help_email', $email);
        }

        return $next($request);
    }
}
