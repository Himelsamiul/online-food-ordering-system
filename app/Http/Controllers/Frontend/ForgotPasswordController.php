<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Registration;
use App\Support\Otp;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ForgotPasswordController extends Controller
{
    /**
     * Step 1: ask for the email.
     */
    public function showRequest()
    {
        return view('frontend.pages.auth.forgot-password');
    }

    /**
     * Step 2: generate a code, store it (hashed), email it.
     */
    public function sendCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = Registration::where('email', $request->email)->first();

        if (!$user) {
            return back()
                ->withInput()
                ->with('error', 'No account found with that email.');
        }

        $code = Otp::generate();

        // Store the code (hashed) in the standard reset-tokens table.
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            ['token' => Hash::make($code), 'created_at' => now()]
        );

        Otp::deliver($user->email, $code, 'reset');

        session()->put('reset_email', $user->email);

        return redirect()->route('password.reset')
            ->with('success', 'We sent a 6-digit reset code to your email.');
    }

    /**
     * Step 3: show the code + new-password form.
     */
    public function showReset()
    {
        $email = session('reset_email');

        if (!$email) {
            return redirect()->route('password.request')
                ->with('error', 'Please request a reset code first.');
        }

        return view('frontend.pages.auth.reset-password', compact('email'));
    }

    /**
     * Step 4: verify the code and set the new password.
     */
    public function reset(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'code'     => 'required|digits:6',
            'password' => 'required|min:6|confirmed',
        ]);

        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$record) {
            return back()->with('error', 'Please request a new reset code.');
        }

        // Expiry: 10 minutes.
        if (Carbon::parse($record->created_at)->addMinutes(Otp::EXPIRES_MINUTES)->isPast()) {
            return back()->with('error', 'The code has expired. Please request a new one.');
        }

        if (!Hash::check($request->code, $record->token)) {
            return back()->with('error', 'Invalid reset code.');
        }

        $user = Registration::where('email', $request->email)->first();

        if (!$user) {
            return redirect()->route('password.request')
                ->with('error', 'Account not found.');
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        // Invalidate the used code.
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();
        session()->forget('reset_email');

        return redirect()->route('login')
            ->with('success', 'Password reset successful. Please log in.');
    }
}
