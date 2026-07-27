<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Tells someone their account was switched on, switched off, or that a request
 * they sent was turned down.
 *
 * Scalars only — see PasswordResetLinkMail for why.
 */
class AccountStatusMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public const ACTIVATED   = 'activated';
    public const DEACTIVATED = 'deactivated';
    public const REJECTED    = 'rejected';

    public function __construct(
        public string $name,
        public string $email,
        public string $state,
        public ?string $note = null,
        public bool $isAdmin = false,
    ) {
    }

    public function build()
    {
        $subject = match ($this->state) {
            self::ACTIVATED   => 'Your account is active again',
            self::DEACTIVATED => 'Your account has been deactivated',
            default           => 'About your account request',
        };

        return $this->subject($subject)->view('emails.account-status');
    }
}
