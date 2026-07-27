<?php

namespace App\Rules;

use App\Models\Registration;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * The address must belong to a real account of the given kind.
 *
 * Both help-request forms require this: the whole point is that the person
 * reviewing it can be confident the request came from the account owner, which
 * only holds if the address on the form is the one on file.
 *
 * This does tell an anonymous submitter whether an address is registered. That
 * is an explicit product requirement ("reject the request immediately with a
 * validation error"), and the endpoints behind it are rate limited, which is
 * what keeps it from being a practical enumeration oracle.
 */
class RegisteredAccountEmail implements ValidationRule
{
    public const ADMIN    = 'admin';
    public const CUSTOMER = 'customer';

    public function __construct(
        private readonly string $accountType = self::CUSTOMER,
        /** null = don't care, true = must be switched off, false = must be active */
        private readonly ?bool $mustBeInactive = null,
    ) {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $email   = mb_strtolower(trim((string) $value));
        $account = $this->find($email);

        if (!$account) {
            $fail($this->accountType === self::ADMIN
                ? 'No admin account is registered with this email address. Enter the exact address your account was created with.'
                : 'No account is registered with this email address. Enter the exact address you signed up with.');

            return;
        }

        if ($this->mustBeInactive === true && $account->isActive()) {
            $fail('That account is already active. Try signing in, or ask for a password reset instead.');

            return;
        }

        if ($this->mustBeInactive === false && !$account->isActive()) {
            $fail('That account is currently deactivated. Request reactivation first.');
        }
    }

    /** @return User|Registration|null */
    private function find(string $email)
    {
        if ($this->accountType === self::ADMIN) {
            return User::where('email', $email)->where('is_admin', true)->first();
        }

        return Registration::where('email', $email)->first();
    }
}
