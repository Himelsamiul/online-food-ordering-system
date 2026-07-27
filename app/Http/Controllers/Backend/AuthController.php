<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\LoginHistoryRecorder;
use App\Support\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(private readonly LoginHistoryRecorder $history)
    {
    }

    public function showLogin()
    {
        if (auth()->check() && auth()->user()->is_admin) {
            return redirect()->route('admin.dashboard');
        }

        return view('backend.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $this->assertNotThrottled($request);

        $email = mb_strtolower(trim($credentials['email']));

        /*
         * Look the account up first so a deactivated admin is told why they are
         * blocked instead of being handed a generic "wrong password". They have
         * to prove the password before they get that information, though —
         * otherwise the login page becomes a way to enumerate disabled accounts.
         */
        $user = User::where('email', $email)->first();

        if (!auth()->attempt(['email' => $email, 'password' => $credentials['password']], $request->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey($request), (int) config('security.login.decay_seconds', 300));

            $this->history->recordAdminAttempt($request, $user, false, 'Invalid credentials');
            AuditLogger::auth(AuditLogger::ACTION_LOGIN_FAILED, 'Failed admin sign-in for ' . $email);

            return back()->withInput($request->only('email'))->withErrors([
                'login' => 'Invalid email or password',
            ]);
        }

        $user = auth()->user();

        if (!$user->is_admin) {
            auth()->logout();
            RateLimiter::hit($this->throttleKey($request), (int) config('security.login.decay_seconds', 300));

            $this->history->recordAdminAttempt($request, $user, false, 'Not an admin account');
            AuditLogger::auth(AuditLogger::ACTION_LOGIN_FAILED, 'Non-admin account tried the admin panel: ' . $email);

            return back()->withErrors(['login' => 'You are not authorized as admin']);
        }

        // Deactivated admins authenticate fine but must not get a session.
        if (!$user->isActive()) {
            $reason = $user->deactivation_reason;

            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $this->history->recordAdminAttempt($request, $user, false, 'Account deactivated');
            AuditLogger::auth(
                AuditLogger::ACTION_LOGIN_FAILED,
                'Deactivated admin ' . $email . ' tried to sign in',
                $user,
            );

            return back()
                ->withInput($request->only('email'))
                ->with('error', 'Your account has been deactivated.'
                    . ($reason ? ' Reason: ' . $reason : ''))
                ->with('offer_activation_help', true)
                ->with('help_email', $email);
        }

        RateLimiter::clear($this->throttleKey($request));

        $request->session()->regenerate();

        $this->history->recordAdminAttempt($request, $user, true);

        AuditLogger::auth(
            AuditLogger::ACTION_LOGIN,
            $user->name . ' signed in to the admin panel',
            $user,
        );

        return redirect()->intended(route('admin.dashboard'))
            ->with('success', 'Welcome back, ' . $user->name . '.');
    }

    public function logout(Request $request)
    {
        $user = auth()->user();

        if ($user) {
            AuditLogger::auth(
                AuditLogger::ACTION_LOGOUT,
                $user->name . ' signed out of the admin panel',
                $user,
            );
        }

        $this->history->closeAdminSession($request, $user);

        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')->with('success', 'Logged out successfully.');
    }

    /**
     * Fixed-window throttle on email+IP. Rejecting with a ValidationException
     * puts the wait time on the form rather than showing a bare 429 page.
     */
    private function assertNotThrottled(Request $request): void
    {
        $key = $this->throttleKey($request);
        $max = (int) config('security.login.max_attempts', 5);

        if (!RateLimiter::tooManyAttempts($key, $max)) {
            return;
        }

        $seconds = RateLimiter::availableIn($key);

        AuditLogger::auth(
            AuditLogger::ACTION_LOGIN_FAILED,
            'Admin sign-in throttled for ' . $request->input('email') . ' from ' . $request->ip(),
        );

        throw ValidationException::withMessages([
            'login' => 'Too many attempts. Try again in ' . ceil($seconds / 60) . ' minute(s).',
        ]);
    }

    private function throttleKey(Request $request): string
    {
        return 'admin-login:' . Str::lower((string) $request->input('email')) . '|' . $request->ip();
    }
}
