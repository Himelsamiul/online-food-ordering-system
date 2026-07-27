<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Registration;
use App\Services\LoginHistoryRecorder;
use App\Support\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * GeoIP has nothing to say about 127.0.0.1, so on a local box a random
     * public address is substituted to keep the location column meaningful
     * while developing. Never used outside the local environment.
     */
    private const SAMPLE_IPS = [
        '8.8.8.8', '99.79.0.1', '81.2.69.142', '91.198.174.192', '62.210.0.1',
        '5.79.64.0', '103.21.244.0', '49.44.0.1', '39.32.0.1', '112.134.0.1',
        '43.224.0.1', '36.112.0.1', '139.99.0.1', '1.1.1.1', '202.89.32.1',
        '191.96.0.1', '190.16.0.1', '190.107.0.1', '187.188.0.1', '102.165.0.1',
        '105.112.0.1', '41.32.0.1', '95.177.0.1', '88.255.0.1', '5.8.0.1',
        '83.44.0.1', '79.0.0.1', '85.224.0.1', '84.48.0.1', '62.236.0.1',
    ];

    public function __construct(private readonly LoginHistoryRecorder $history)
    {
    }

    public function showLogin()
    {
        if (Auth::guard('frontend')->check()) {
            return redirect()->route('profile');
        }

        return view('frontend.pages.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'login'    => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $this->assertNotThrottled($request);

        // Username OR email.
        $credentials = filter_var($request->login, FILTER_VALIDATE_EMAIL)
            ? ['email' => mb_strtolower(trim($request->login)), 'password' => $request->password]
            : ['username' => $request->login, 'password' => $request->password];

        /*
         * Both failure paths redirect to the login route by name rather than
         * back(). back() resolves off the Referer header, and when that is
         * missing it silently falls through to "/" — dropping the customer on
         * the home page with a flash they cannot act on.
         */
        if (!Auth::guard('frontend')->attempt($credentials, $request->filled('remember'))) {
            RateLimiter::hit($this->throttleKey($request), (int) config('security.login.decay_seconds', 300));

            AuditLogger::auth(
                AuditLogger::ACTION_LOGIN_FAILED,
                'Failed customer sign-in for ' . $request->input('login'),
            );

            return redirect()->route('login')
                ->withInput($request->only('login'))
                ->with('error', 'Invalid username/email or password')
                ->with('offer_password_help', true);
        }

        /** @var Registration $user */
        $user = Auth::guard('frontend')->user();

        // Deactivated accounts authenticate fine but must not get a session.
        if (!$user->isActive()) {
            $email  = $user->email;
            $reason = $user->deactivation_reason;

            Auth::guard('frontend')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            AuditLogger::auth(
                AuditLogger::ACTION_LOGIN_FAILED,
                'Deactivated customer ' . $email . ' tried to sign in',
                $user,
            );

            return redirect()->route('login')
                ->withInput($request->only('login'))
                ->with('error', 'Your account is currently inactive.'
                    . ($reason ? ' Reason: ' . $reason : '')
                    . ' To use it again you need an admin to reactivate it.')
                ->with('offer_activation_help', true)
                ->with('help_email', $email);
        }

        RateLimiter::clear($this->throttleKey($request));

        $request->session()->regenerate();

        $this->history->recordCustomerLogin(
            $request,
            $user,
            app()->environment('local') ? self::SAMPLE_IPS[array_rand(self::SAMPLE_IPS)] : null,
        );

        AuditLogger::auth(AuditLogger::ACTION_LOGIN, $user->full_name . ' signed in', $user);

        return redirect()->intended(route('profile'))->with('success', 'Login successful');
    }

    public function logout(Request $request)
    {
        /** @var Registration|null $user */
        $user = Auth::guard('frontend')->user();

        if ($user) {
            AuditLogger::auth(AuditLogger::ACTION_LOGOUT, $user->full_name . ' signed out', $user);
        }

        $this->history->closeCustomerSession($request, $user);

        Auth::guard('frontend')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Logged out successfully');
    }

    /* ------------------------------------------------------------ throttle */

    private function assertNotThrottled(Request $request): void
    {
        $key = $this->throttleKey($request);
        $max = (int) config('security.login.max_attempts', 5);

        if (!RateLimiter::tooManyAttempts($key, $max)) {
            return;
        }

        AuditLogger::auth(
            AuditLogger::ACTION_LOGIN_FAILED,
            'Customer sign-in throttled for ' . $request->input('login') . ' from ' . $request->ip(),
        );

        throw ValidationException::withMessages([
            'login' => 'Too many attempts. Try again in '
                . ceil(RateLimiter::availableIn($key) / 60) . ' minute(s).',
        ]);
    }

    private function throttleKey(Request $request): string
    {
        return 'customer-login:' . Str::lower((string) $request->input('login')) . '|' . $request->ip();
    }
}
