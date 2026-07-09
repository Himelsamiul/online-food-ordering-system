<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $code;
    public string $purpose;      // "register" | "reset"
    public int $expiresMinutes;

    public function __construct(string $code, string $purpose, int $expiresMinutes = 10)
    {
        $this->code = $code;
        $this->purpose = $purpose;
        $this->expiresMinutes = $expiresMinutes;
    }

    public function build()
    {
        $subject = $this->purpose === 'reset'
            ? 'Your password reset code'
            : 'Verify your email address';

        return $this->subject($subject)
            ->view('emails.otp');
    }
}
