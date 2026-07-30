<?php

namespace App\Http\Controllers\Rider;

use App\Http\Controllers\Controller;
use App\Models\DeliveryMan;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::guard('rider')->check()) {
            return redirect()->route('rider.dashboard');
        }

        return view('rider.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'login'    => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $this->assertNotThrottled($request);

        // Riders are given a username by the office; an email also works for
        // the ones who have one on file.
        $field = filter_var($credentials['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $rider = DeliveryMan::where($field, $credentials['login'])->first();

        if (!$rider || !Auth::guard('rider')->attempt(
            [$field => $credentials['login'], 'password' => $credentials['password']],
            $request->boolean('remember')
        )) {
            RateLimiter::hit($this->throttleKey($request), 300);

            AuditLogger::auth(
                AuditLogger::ACTION_LOGIN_FAILED,
                'Failed rider sign-in for ' . $credentials['login'],
            );

            return redirect()->route('rider.login')
                ->withInput($request->only('login'))
                ->withErrors(['login' => 'Wrong username or password.']);
        }

        $rider = Auth::guard('rider')->user();

        /*
         * A switched-off rider authenticates fine but must not get a session —
         * the same rule the admin and customer guards use.
         */
        if (!$rider->isActive()) {
            Auth::guard('rider')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            AuditLogger::auth(
                AuditLogger::ACTION_LOGIN_FAILED,
                "Switched-off rider {$rider->name} tried to sign in",
                $rider,
            );

            return redirect()->route('rider.login')
                ->with('error', 'Your rider account has been switched off. Contact the office.');
        }

        RateLimiter::clear($this->throttleKey($request));
        $request->session()->regenerate();

        $rider->forceFill([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ])->save();

        AuditLogger::auth(AuditLogger::ACTION_LOGIN, "Rider {$rider->name} signed in", $rider);

        return redirect()->route('rider.dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        $rider = Auth::guard('rider')->user();

        if ($rider) {
            AuditLogger::auth(AuditLogger::ACTION_LOGOUT, "Rider {$rider->name} signed out", $rider);
        }

        Auth::guard('rider')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('rider.login')->with('success', 'Signed out.');
    }

    /* -------------------------------------------------------------- throttle */

    private function assertNotThrottled(Request $request): void
    {
        $key = $this->throttleKey($request);

        if (!RateLimiter::tooManyAttempts($key, 5)) {
            return;
        }

        $seconds = RateLimiter::availableIn($key);

        throw ValidationException::withMessages([
            'login' => "Too many attempts. Try again in {$seconds} seconds.",
        ]);
    }

    private function throttleKey(Request $request): string
    {
        return 'rider-login|' . Str::lower((string) $request->input('login')) . '|' . $request->ip();
    }
}
