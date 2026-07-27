<?php

namespace App\Http\Controllers\Backend\Auth;

use App\Http\Controllers\Controller;
use App\Models\OtpCode;
use App\Models\User;
use App\Services\OtpService;
use App\Services\PasswordResetLinkService;
use App\Support\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Redeems the single-use reset link a superadmin issued to an admin.
 *
 * The route itself carries Laravel's `signed` middleware, so a tampered or
 * expired URL never reaches this controller. What is left to check here is
 * whether the token still matches an unused row — the second, independent lock.
 */
class ResetLinkController extends Controller
{
    public function __construct(private readonly PasswordResetLinkService $links)
    {
    }

    public function show(Request $request)
    {
        $email = (string) $request->query('email');
        $token = (string) $request->query('token');

        if (!$this->links->resolve(PasswordResetLinkService::GUARD_ADMIN, $email, $token)) {
            return redirect()->route('admin.login')->with(
                'error',
                'That reset link has already been used or has expired. Please request assistance again.'
            );
        }

        return view('backend.auth.reset-link', [
            'email' => $email,
            'token' => $token,
            'url'   => $request->fullUrl(),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'email'    => ['required', 'email'],
            'token'    => ['required', 'string'],
            'password' => [
                'required', 'confirmed',
                'min:' . config('security.password_reset.min_password_length', 8),
            ],
        ]);

        $link = $this->links->resolve(
            PasswordResetLinkService::GUARD_ADMIN,
            $data['email'],
            $data['token'],
        );

        if (!$link) {
            return redirect()->route('admin.login')->with(
                'error',
                'That reset link has already been used or has expired.'
            );
        }

        $admin = User::find($link->account_id);

        if (!$admin || !$admin->is_admin) {
            return redirect()->route('admin.login')->with('error', 'Account not found.');
        }

        DB::transaction(function () use ($admin, $data, $link) {
            $admin->forceFill([
                'password'            => Hash::make($data['password']),
                'password_changed_at' => now(),
            ])->save();

            $this->links->consume($link);

            // Any outstanding email code for the same account also stops working.
            OtpCode::for(OtpService::GUARD_ADMIN, OtpService::PURPOSE_RESET, $admin->email)
                ->usable()
                ->update(['consumed_at' => now()]);

            AuditLogger::auth(
                AuditLogger::ACTION_PASSWORD_RESET,
                $admin->name . ' set a new password using the reset link issued by '
                    . ($link->issued_by_name ?: 'a super admin'),
                $admin,
            );
        });

        return redirect()->route('admin.login')->with(
            'success',
            'Your password has been set. You can sign in now.'
        );
    }
}
