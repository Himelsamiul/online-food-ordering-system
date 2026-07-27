<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Sent when a superadmin (or an authorised admin) approves a password
 * assistance request.
 *
 * Carries a signed, single-use, expiring link — never a password. The recipient
 * chooses their own password at the other end, so nothing reusable ever exists
 * in an inbox.
 *
 * Scalars only, no models: the job is serialised onto the queue and a model
 * that gets deleted before the worker picks it up would make the mail fail.
 */
class PasswordResetLinkMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $name,
        public string $email,
        public string $url,
        public int $expiresMinutes,
        public ?string $note = null,
        public bool $isAdmin = true,
    ) {
    }

    public function build()
    {
        return $this->subject('Set a new password for your account')
            ->view('emails.password-reset-link');
    }
}
