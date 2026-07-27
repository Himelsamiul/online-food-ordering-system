<?php

namespace App\Services;

use App\Mail\OtpMail;
use App\Models\OtpCode;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Issues and verifies one-time passcodes for every guard and purpose.
 *
 * Guarantees:
 *   - the plain code exists only in the email and in memory; the row holds a hash
 *   - a code is single use: consuming it stamps consumed_at, and issuing a new
 *     one for the same target burns every earlier outstanding code
 *   - expiry, attempt ceiling and resend interval are all config-driven
 *   - wrong guesses are counted per code and the code is burned at the ceiling,
 *     so a 6-digit space cannot be walked
 */
class OtpService
{
    public const GUARD_ADMIN    = 'web';
    public const GUARD_CUSTOMER = 'frontend';

    // Mirrored from OtpCode so callers only need to know about the service.
    public const PURPOSE_REGISTER = OtpCode::PURPOSE_REGISTER;
    public const PURPOSE_RESET    = OtpCode::PURPOSE_RESET;

    // verify() outcomes
    public const OK           = 'ok';
    public const INVALID      = 'invalid';
    public const EXPIRED      = 'expired';
    public const NOT_FOUND    = 'not_found';
    public const TOO_MANY     = 'too_many_attempts';

    /**
     * Mint a code, store its hash, email it.
     *
     * @return string|null the plain code, or null when the caller is throttled
     */
    public function issue(string $guard, string $purpose, string $email, ?string $ip = null): ?string
    {
        $email = mb_strtolower(trim($email));

        if (!$this->canIssue($guard, $purpose, $email)) {
            return null;
        }

        $code = $this->generate();

        // Anything still outstanding for this target stops working now — two
        // live codes would double the guessing surface for no benefit.
        OtpCode::for($guard, $purpose, $email)->usable()->update(['consumed_at' => now()]);

        OtpCode::create([
            'guard'      => $guard,
            'purpose'    => $purpose,
            'identifier' => $email,
            'code_hash'  => Hash::make($code),
            'attempts'   => 0,
            'expires_at' => now()->addMinutes($this->expiryMinutes()),
            'ip_address' => $ip ?: request()->ip(),
        ]);

        RateLimiter::hit($this->throttleKey($guard, $purpose, $email), 3600);

        $this->deliver($email, $code, $purpose);

        return $code;
    }

    /**
     * Check a submitted code WITHOUT consuming it.
     *
     * Splitting verify from consume lets the reset flow prove the code at the
     * "enter your code" step and only burn it once the new password is actually
     * saved — so a validation failure on the password does not strand the user.
     *
     * @return array{status: string, otp: OtpCode|null, remaining: int}
     */
    public function verify(string $guard, string $purpose, string $email, string $code): array
    {
        $email = mb_strtolower(trim($email));

        /** @var OtpCode|null $otp */
        $otp = OtpCode::for($guard, $purpose, $email)->latest('id')->first();

        if (!$otp) {
            return ['status' => self::NOT_FOUND, 'otp' => null, 'remaining' => 0];
        }

        if ($otp->isConsumed()) {
            return ['status' => self::NOT_FOUND, 'otp' => $otp, 'remaining' => 0];
        }

        if ($otp->isExpired()) {
            return ['status' => self::EXPIRED, 'otp' => $otp, 'remaining' => 0];
        }

        if ($otp->attempts >= $this->maxAttempts()) {
            $otp->update(['consumed_at' => now()]);

            return ['status' => self::TOO_MANY, 'otp' => $otp, 'remaining' => 0];
        }

        if (!Hash::check($code, $otp->code_hash)) {
            $otp->increment('attempts');

            $remaining = max(0, $this->maxAttempts() - $otp->attempts);

            if ($remaining === 0) {
                $otp->update(['consumed_at' => now()]);

                return ['status' => self::TOO_MANY, 'otp' => $otp, 'remaining' => 0];
            }

            return ['status' => self::INVALID, 'otp' => $otp, 'remaining' => $remaining];
        }

        return ['status' => self::OK, 'otp' => $otp, 'remaining' => $this->maxAttempts() - $otp->attempts];
    }

    /** Burn a verified code so it can never be replayed. */
    public function consume(OtpCode $otp): void
    {
        $otp->update(['consumed_at' => now()]);
    }

    /**
     * Verify and burn in one step, for callers that have nothing left to fail at.
     *
     * @return array{status: string, otp: OtpCode|null, remaining: int}
     */
    public function verifyAndConsume(string $guard, string $purpose, string $email, string $code): array
    {
        $result = $this->verify($guard, $purpose, $email, $code);

        if ($result['status'] === self::OK && $result['otp']) {
            $this->consume($result['otp']);
        }

        return $result;
    }

    /** False when the caller has asked for codes too often or too recently. */
    public function canIssue(string $guard, string $purpose, string $email): bool
    {
        $email = mb_strtolower(trim($email));

        if (RateLimiter::tooManyAttempts($this->throttleKey($guard, $purpose, $email), $this->maxPerHour())) {
            return false;
        }

        return $this->secondsUntilResend($guard, $purpose, $email) === 0;
    }

    /** How long the caller must wait before a fresh code will be issued. */
    public function secondsUntilResend(string $guard, string $purpose, string $email): int
    {
        $latest = OtpCode::for($guard, $purpose, mb_strtolower(trim($email)))
            ->latest('id')
            ->first();

        if (!$latest) {
            return 0;
        }

        $ready = $latest->created_at->addSeconds($this->resendSeconds());

        return $ready->isFuture() ? (int) ceil(now()->diffInSeconds($ready)) : 0;
    }

    public function expiryMinutes(): int
    {
        return (int) config('security.otp.expires_minutes', 10);
    }

    public function maxAttempts(): int
    {
        return (int) config('security.otp.max_attempts', 5);
    }

    public function resendSeconds(): int
    {
        return (int) config('security.otp.resend_seconds', 60);
    }

    private function maxPerHour(): int
    {
        return (int) config('security.otp.throttle.max_per_hour', 6);
    }

    private function throttleKey(string $guard, string $purpose, string $email): string
    {
        return 'otp:' . $guard . ':' . $purpose . ':' . sha1($email . '|' . request()->ip());
    }

    private function generate(): string
    {
        $length = max(4, (int) config('security.otp.length', 6));
        $max    = (10 ** $length) - 1;

        return str_pad((string) random_int(0, $max), $length, '0', STR_PAD_LEFT);
    }

    /**
     * Queued so a slow SMTP handshake never holds up the response. A transport
     * failure is logged, not thrown — the user has already been told a code is
     * on the way and can ask for another.
     */
    private function deliver(string $email, string $code, string $purpose): void
    {
        try {
            Mail::to($email)->queue(new OtpMail($code, $purpose, $this->expiryMinutes()));

            AuditLogger::system(
                AuditLogger::ACTION_OTP_SENT,
                'Authentication',
                "Sent a {$purpose} code to {$email}",
            );
        } catch (\Throwable $e) {
            Log::warning('OTP mail failed: ' . $e->getMessage());
        }
    }
}
