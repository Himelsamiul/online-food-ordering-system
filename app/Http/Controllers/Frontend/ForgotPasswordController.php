<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\OtpCode;
use App\Models\PasswordResetLink;
use App\Models\Registration;
use App\Services\OtpService;
use App\Support\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Customer self-service password reset.
 *
 * Email → 6-digit code → new password. The code is single use, expires on a
 * configurable timer, tolerates a bounded number of wrong guesses, and cannot
 * be requested faster than the resend interval.
 *
 * Each step has its own session gate so the flow cannot be entered halfway:
 * knowing the reset URL is not enough, you have to have passed the step before.
 */
class ForgotPasswordController extends Controller
{
    private const SESSION_EMAIL    = 'reset_email';
    private const SESSION_VERIFIED = 'reset_verified';
    private const SESSION_OTP_ID   = 'reset_otp_id';

    public function __construct(private readonly OtpService $otp)
    {
    }

    /* ------------------------------------------------------------- step 1 */

    public function showRequest()
    {
        return view('frontend.pages.auth.forgot-password');
    }

    public function sendCode(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $email = mb_strtolower(trim($data['email']));
        $user  = Registration::where('email', $email)->first();

        if (!$user) {
            // Same answer either way — this form must not reveal which
            // addresses have accounts.
            return redirect()->route('password.verify')->with(
                'success',
                'If that email is registered, a reset code is on its way.'
            );
        }

        if (!$user->isActive()) {
            return back()->withInput()->with(
                'error',
                'That account is deactivated, so its password cannot be reset. Ask an admin to switch it back on first.'
            )->with('offer_activation_help', true)->with('help_email', $email);
        }

        $code = $this->otp->issue(OtpService::GUARD_CUSTOMER, OtpService::PURPOSE_RESET, $email);

        if ($code === null) {
            $wait = $this->otp->secondsUntilResend(OtpService::GUARD_CUSTOMER, OtpService::PURPOSE_RESET, $email);

            return back()->withInput()->with(
                'error',
                $wait > 0
                    ? "A code was just sent. Please wait {$wait} seconds before asking for another."
                    : 'Too many codes have been requested for this address. Please try again later.'
            );
        }

        $request->session()->put(self::SESSION_EMAIL, $email);
        $request->session()->forget([self::SESSION_VERIFIED, self::SESSION_OTP_ID]);

        AuditLogger::auth(
            AuditLogger::ACTION_RESET_REQUESTED,
            'Customer ' . $email . ' requested a password reset code',
            $user,
        );

        return redirect()->route('password.verify')
            ->with('success', 'We sent a ' . strlen($code) . '-digit code to ' . $email . '.');
    }

    /* ------------------------------------------------------------- step 2 */

    public function showVerify(Request $request)
    {
        $email = $request->session()->get(self::SESSION_EMAIL);

        if (!$email) {
            return redirect()->route('password.request')
                ->with('error', 'Please request a reset code first.');
        }

        return view('frontend.pages.auth.verify-code', [
            'email'          => $email,
            'expiresMinutes' => $this->otp->expiryMinutes(),
        ]);
    }

    public function verify(Request $request)
    {
        $email = $request->session()->get(self::SESSION_EMAIL);

        if (!$email) {
            return redirect()->route('password.request')
                ->with('error', 'Please request a reset code first.');
        }

        $data = $request->validate([
            'code' => ['required', 'digits:' . config('security.otp.length', 6)],
        ]);

        $result = $this->otp->verify(OtpService::GUARD_CUSTOMER, OtpService::PURPOSE_RESET, $email, $data['code']);

        if ($result['status'] !== OtpService::OK) {
            return back()->with('error', $this->messageFor($result));
        }

        // Proven, not burned — the code is consumed once the new password is
        // saved, so a failed password rule does not strand the customer.
        $request->session()->put(self::SESSION_VERIFIED, true);
        $request->session()->put(self::SESSION_OTP_ID, $result['otp']->id);

        return redirect()->route('password.reset')
            ->with('success', 'Code accepted. Choose a new password.');
    }

    public function resend(Request $request)
    {
        $email = $request->session()->get(self::SESSION_EMAIL);

        if (!$email) {
            return redirect()->route('password.request');
        }

        $code = $this->otp->issue(OtpService::GUARD_CUSTOMER, OtpService::PURPOSE_RESET, $email);

        if ($code === null) {
            $wait = $this->otp->secondsUntilResend(OtpService::GUARD_CUSTOMER, OtpService::PURPOSE_RESET, $email);

            return back()->with('error', $wait > 0
                ? "Please wait {$wait} seconds before asking for another code."
                : 'Too many codes requested. Try again later.');
        }

        return back()->with('success', 'A new code is on its way.');
    }

    /* ------------------------------------------------------------- step 3 */

    public function showReset(Request $request)
    {
        if (!$request->session()->get(self::SESSION_VERIFIED)) {
            return redirect()->route('password.request')
                ->with('error', 'Please verify your code first.');
        }

        return view('frontend.pages.auth.reset-password', [
            'email' => $request->session()->get(self::SESSION_EMAIL),
        ]);
    }

    public function reset(Request $request)
    {
        $email = $request->session()->get(self::SESSION_EMAIL);

        if (!$email || !$request->session()->get(self::SESSION_VERIFIED)) {
            return redirect()->route('password.request')->with('error', 'Please start again.');
        }

        $data = $request->validate([
            'password' => [
                'required', 'confirmed',
                'min:' . config('security.password_reset.min_password_length', 8),
            ],
        ]);

        $user = Registration::where('email', $email)->first();

        if (!$user) {
            $this->clear($request);

            return redirect()->route('password.request')->with('error', 'Account not found.');
        }

        // The verified session must never outlive the code that earned it.
        $otp = OtpCode::find($request->session()->get(self::SESSION_OTP_ID));

        if (!$otp || !$otp->isUsable() || $otp->identifier !== $email) {
            $this->clear($request);

            return redirect()->route('password.request')
                ->with('error', 'That code is no longer valid. Please start again.');
        }

        DB::transaction(function () use ($user, $data, $email) {
            $user->forceFill(['password' => Hash::make($data['password'])])->save();

            OtpCode::for(OtpService::GUARD_CUSTOMER, OtpService::PURPOSE_RESET, $email)
                ->usable()
                ->update(['consumed_at' => now()]);

            PasswordResetLink::for(OtpService::GUARD_CUSTOMER, $email)
                ->usable()
                ->update(['used_at' => now()]);

            AuditLogger::auth(
                AuditLogger::ACTION_PASSWORD_RESET,
                'Customer ' . $email . ' reset their password with an email code',
                $user,
            );
        });

        $this->clear($request);

        return redirect()->route('login')
            ->with('success', 'Password reset successful. Please log in.');
    }

    /* ------------------------------------------------------------ helpers */

    private function clear(Request $request): void
    {
        $request->session()->forget([self::SESSION_EMAIL, self::SESSION_VERIFIED, self::SESSION_OTP_ID]);
    }

    /**
     * @param  array{status: string, otp: mixed, remaining: int}  $result
     */
    private function messageFor(array $result): string
    {
        return match ($result['status']) {
            OtpService::EXPIRED   => 'That code has expired. Ask for a new one.',
            OtpService::TOO_MANY  => 'Too many wrong attempts — that code has been cancelled. Request a new one.',
            OtpService::NOT_FOUND => 'No active code for this address. Request a new one.',
            default               => 'That code is not right. ' . $result['remaining'] . ' attempt(s) left.',
        };
    }
}
