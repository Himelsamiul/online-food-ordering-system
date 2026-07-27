<?php

namespace App\Services;

use App\Models\AccountRequest;
use App\Models\PasswordResetLink;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

/**
 * Mints and redeems single-use password reset links.
 *
 * Two independent locks guard the link:
 *   - Laravel's signed URL, so the query string cannot be edited at all
 *   - a random token whose hash is the only copy we keep, checked against the
 *     row and burned on use
 *
 * Either one alone would be adequate; together a leaked database still cannot
 * be turned into a working link, and a tampered URL never reaches the lookup.
 */
class PasswordResetLinkService
{
    public const GUARD_ADMIN    = 'web';
    public const GUARD_CUSTOMER = 'frontend';

    /**
     * Issue a link for an admin.
     *
     * @return array{url: string, link: PasswordResetLink}
     */
    public function issueForAdmin(User $admin, ?AccountRequest $request = null, ?User $issuer = null): array
    {
        return $this->issue(
            self::GUARD_ADMIN,
            $admin,
            $admin->email,
            'admin.password.reset.link',
            $request,
            $issuer,
        );
    }

    /**
     * Issue a link for a customer.
     *
     * @return array{url: string, link: PasswordResetLink}
     */
    public function issueForCustomer(Registration $customer, ?AccountRequest $request = null, ?User $issuer = null): array
    {
        return $this->issue(
            self::GUARD_CUSTOMER,
            $customer,
            $customer->email,
            'password.reset.link',
            $request,
            $issuer,
        );
    }

    /**
     * Find the row a submitted token belongs to, or null.
     *
     * Every outstanding link for the address is checked rather than only the
     * newest, so re-issuing does not silently break a link already in flight.
     */
    public function resolve(string $guard, string $email, string $token): ?PasswordResetLink
    {
        $candidates = PasswordResetLink::for($guard, $email)->usable()->latest('id')->get();

        foreach ($candidates as $link) {
            if (Hash::check($token, $link->token_hash)) {
                return $link;
            }
        }

        return null;
    }

    /** Burn the link and every sibling still outstanding for that account. */
    public function consume(PasswordResetLink $link): void
    {
        PasswordResetLink::where('guard', $link->guard)
            ->where('account_id', $link->account_id)
            ->whereNull('used_at')
            ->update([
                'used_at' => now(),
                'used_ip' => request()->ip(),
            ]);
    }

    public function expiryMinutes(): int
    {
        return (int) config('security.password_reset.link_expires_minutes', 60);
    }

    /**
     * @return array{url: string, link: PasswordResetLink}
     */
    private function issue(
        string $guard,
        Model $account,
        string $email,
        string $routeName,
        ?AccountRequest $request,
        ?User $issuer,
    ): array {
        // Anything already outstanding stops working — one live link at a time.
        PasswordResetLink::where('guard', $guard)
            ->where('account_id', $account->getKey())
            ->whereNull('used_at')
            ->update(['used_at' => now()]);

        $token   = Str::random(64);
        $expires = now()->addMinutes($this->expiryMinutes());

        $link = PasswordResetLink::create([
            'guard'              => $guard,
            'account_id'         => $account->getKey(),
            'email'              => $email,
            'token_hash'         => Hash::make($token),
            'account_request_id' => $request?->id,
            'issued_by'          => $issuer?->id,
            'issued_by_name'     => $issuer?->name,
            'expires_at'         => $expires,
        ]);

        $url = URL::temporarySignedRoute($routeName, $expires, [
            'token' => $token,
            'email' => $email,
        ]);

        return ['url' => $url, 'link' => $link];
    }
}
