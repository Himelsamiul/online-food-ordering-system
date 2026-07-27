<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /** @param string $purpose "register" | "password_reset" */
    public function __construct(
        public string $code,
        public string $purpose,
        public int $expiresMinutes = 10,
    ) {
    }

    public function build()
    {
        $subject = $this->purpose === 'register'
            ? 'Verify your email address'
            : 'Your password reset code';

        return $this->subject($subject)->view('emails.otp');
    }
}
