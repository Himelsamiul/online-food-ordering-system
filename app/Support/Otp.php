<?php

namespace App\Support;

use App\Mail\OtpMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class Otp
{
    public const EXPIRES_MINUTES = 10;

    /**
     * Generate a 6-digit numeric code.
     */
    public static function generate(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Email the code to the user.
     */
    public static function deliver(string $email, string $code, string $purpose): void
    {
        try {
            Mail::to($email)->send(new OtpMail($code, $purpose, self::EXPIRES_MINUTES));
        } catch (\Throwable $e) {
            // Never break the flow if mail transport hiccups.
            Log::warning('OTP mail failed: ' . $e->getMessage());
        }
    }
}
