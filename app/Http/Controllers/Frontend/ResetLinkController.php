<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\OtpCode;
use App\Models\Registration;
use App\Services\OtpService;
use App\Services\PasswordResetLinkService;
use App\Support\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Redeems the single-use reset link an admin issued to a customer.
 *
 * The route carries Laravel's `signed` middleware, so a tampered or expired URL
 * never reaches here. What is left is checking the token against an unused row.
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

        if (!$this->links->resolve(PasswordResetLinkService::GUARD_CUSTOMER, $email, $token)) {
            return redirect()->route('login')->with(
                'error',
                'That reset link has already been used or has expired. Please request another.'
            );
        }

        return view('frontend.pages.auth.reset-link', [
            'email' => $email,
            'token' => $token,
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
            PasswordResetLinkService::GUARD_CUSTOMER,
            $data['email'],
            $data['token'],
        );

        if (!$link) {
            return redirect()->route('login')->with(
                'error',
                'That reset link has already been used or has expired.'
            );
        }

        $customer = Registration::find($link->account_id);

        if (!$customer) {
            return redirect()->route('login')->with('error', 'Account not found.');
        }

        DB::transaction(function () use ($customer, $data, $link) {
            $customer->forceFill(['password' => Hash::make($data['password'])])->save();

            $this->links->consume($link);

            OtpCode::for(OtpService::GUARD_CUSTOMER, OtpService::PURPOSE_RESET, $customer->email)
                ->usable()
                ->update(['consumed_at' => now()]);

            AuditLogger::auth(
                AuditLogger::ACTION_PASSWORD_RESET,
                $customer->full_name . ' set a new password using the link issued by '
                    . ($link->issued_by_name ?: 'an administrator'),
                $customer,
            );
        });

        return redirect()->route('login')->with(
            'success',
            'Your password has been set. You can sign in now.'
        );
    }
}
