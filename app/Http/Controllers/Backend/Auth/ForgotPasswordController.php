<?php

namespace App\Http\Controllers\Backend\Auth;

use App\Http\Controllers\Controller;
use App\Models\OtpCode;
use App\Models\PasswordResetLink;
use App\Models\User;
use App\Services\OtpService;
use App\Support\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Self-service password reset — SUPER ADMIN ONLY.
 *
 * Every other admin goes through the assistance request instead
 * (PasswordAssistanceController), so a compromised inbox on a staff account
 * cannot be turned into a panel login without a superadmin approving it.
 *
 * Three steps, each with its own session gate, so the flow cannot be entered
 * halfway: email → code → new password.
 */
class ForgotPasswordController extends Controller
{
    private const SESSION_EMAIL    = 'admin_reset_email';
    private const SESSION_VERIFIED = 'admin_reset_verified';
    private const SESSION_OTP_ID   = 'admin_reset_otp_id';

    public function __construct(private readonly OtpService $otp)
    {
    }

    /* ------------------------------------------------------------- step 1 */

    public function showRequest()
    {
        return view('backend.auth.forgot-password');
    }

    public function sendCode(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $email = mb_strtolower(trim($data['email']));
        $admin = User::where('email', $email)->where('is_admin', true)->first();

        // Only a superadmin may reset themselves. Everyone else — including an
        // address that matches nothing — gets the same answer, so this page
        // cannot be used to find out who the superadmins are.
        if (!$admin || !$admin->isSuperadmin()) {
            AuditLogger::auth(
                AuditLogger::ACTION_RESET_REQUESTED,
                'Rejected a super admin password reset attempt for ' . $email,
            );

            return back()->withInput()->with(
                'error',
                'This is only available to super admins. If you manage this panel as a regular admin, '
                . 'use "Request password assistance" instead.'
            );
        }

        if (!$admin->isActive()) {
            return back()->withInput()->with(
                'error',
                'That account is deactivated. Request account activation first.'
            );
        }

        $code = $this->otp->issue(OtpService::GUARD_ADMIN, OtpService::PURPOSE_RESET, $email);

        if ($code === null) {
            $wait = $this->otp->secondsUntilResend(OtpService::GUARD_ADMIN, OtpService::PURPOSE_RESET, $email);

            return back()->withInput()->with(
                'error',
                $wait > 0
                    ? "A code was just sent. Please wait {$wait} seconds before asking for another."
                    : 'Too many reset codes have been requested for this address. Try again later.'
            );
        }

        $request->session()->put(self::SESSION_EMAIL, $email);
        $request->session()->forget(self::SESSION_VERIFIED);

        AuditLogger::auth(
            AuditLogger::ACTION_RESET_REQUESTED,
            'Super admin ' . $email . ' requested a password reset code',
            $admin,
        );

        return redirect()->route('admin.password.verify')
            ->with('success', 'We sent a ' . strlen($code) . '-digit code to ' . $email . '.');
    }

    /* ------------------------------------------------------------- step 2 */

    public function showVerify(Request $request)
    {
        if (!$request->session()->has(self::SESSION_EMAIL)) {
            return redirect()->route('admin.password.request')
                ->with('error', 'Please request a reset code first.');
        }

        return view('backend.auth.verify-code', [
            'email'          => $request->session()->get(self::SESSION_EMAIL),
            'expiresMinutes' => $this->otp->expiryMinutes(),
        ]);
    }

    public function verify(Request $request)
    {
        $email = $request->session()->get(self::SESSION_EMAIL);

        if (!$email) {
            return redirect()->route('admin.password.request')
                ->with('error', 'Please request a reset code first.');
        }

        $data = $request->validate([
            'code' => ['required', 'digits:' . config('security.otp.length', 6)],
        ]);

        $result = $this->otp->verify(OtpService::GUARD_ADMIN, OtpService::PURPOSE_RESET, $email, $data['code']);

        if ($result['status'] !== OtpService::OK) {
            AuditLogger::auth(
                AuditLogger::ACTION_LOGIN_FAILED,
                'Bad password reset code submitted for ' . $email . ' (' . $result['status'] . ')',
            );

            return back()->with('error', $this->messageFor($result));
        }

        // Proven, not burned — the code is consumed once the new password is
        // actually saved, so a validation failure there does not strand anyone.
        // The id is carried so step 3 can confirm it is still the live code.
        $request->session()->put(self::SESSION_VERIFIED, true);
        $request->session()->put(self::SESSION_OTP_ID, $result['otp']->id);

        return redirect()->route('admin.password.reset')
            ->with('success', 'Code accepted. Choose a new password.');
    }

    public function resend(Request $request)
    {
        $email = $request->session()->get(self::SESSION_EMAIL);

        if (!$email) {
            return redirect()->route('admin.password.request');
        }

        $code = $this->otp->issue(OtpService::GUARD_ADMIN, OtpService::PURPOSE_RESET, $email);

        if ($code === null) {
            $wait = $this->otp->secondsUntilResend(OtpService::GUARD_ADMIN, OtpService::PURPOSE_RESET, $email);

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
            return redirect()->route('admin.password.request')
                ->with('error', 'Please verify your code first.');
        }

        return view('backend.auth.reset-password', [
            'email' => $request->session()->get(self::SESSION_EMAIL),
        ]);
    }

    public function reset(Request $request)
    {
        $email = $request->session()->get(self::SESSION_EMAIL);

        if (!$email || !$request->session()->get(self::SESSION_VERIFIED)) {
            return redirect()->route('admin.password.request')
                ->with('error', 'Please start again.');
        }

        $data = $request->validate([
            'password' => [
                'required', 'confirmed',
                'min:' . config('security.password_reset.min_password_length', 8),
            ],
        ]);

        $admin = User::where('email', $email)->where('is_admin', true)->first();

        if (!$admin || !$admin->isSuperadmin()) {
            $this->clear($request);

            return redirect()->route('admin.password.request')->with('error', 'Account not found.');
        }

        /*
         * Re-check the code at the moment of change. Between step 2 and here it
         * could have expired or been superseded by a resend, and a verified
         * session must never outlive the code that earned it.
         */
        $otp = OtpCode::find($request->session()->get(self::SESSION_OTP_ID));

        if (!$otp || !$otp->isUsable() || $otp->identifier !== $email) {
            $this->clear($request);

            return redirect()->route('admin.password.request')
                ->with('error', 'That code is no longer valid. Please start again.');
        }

        DB::transaction(function () use ($admin, $data, $email) {
            $admin->forceFill([
                'password'            => Hash::make($data['password']),
                'password_changed_at' => now(),
            ])->save();

            // Any outstanding code or link for this account stops working.
            OtpCode::for(OtpService::GUARD_ADMIN, OtpService::PURPOSE_RESET, $email)
                ->usable()
                ->update(['consumed_at' => now()]);

            PasswordResetLink::for('web', $email)
                ->usable()
                ->update(['used_at' => now()]);

            AuditLogger::auth(
                AuditLogger::ACTION_PASSWORD_RESET,
                'Super admin ' . $email . ' reset their own password by email code',
                $admin,
            );
        });

        $this->clear($request);

        return redirect()->route('admin.login')
            ->with('success', 'Password updated. You can sign in now.');
    }

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
