<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VerifyRegistrationMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $name;
    public string $verifyUrl;

    public function __construct(string $name, string $verifyUrl)
    {
        $this->name = $name;
        $this->verifyUrl = $verifyUrl;
    }

    public function build(): self
    {
        return $this->subject('Verify your email')
            ->view('emails.verify-registration')
            ->with(['name' => $this->name, 'verifyUrl' => $this->verifyUrl]);
    }
}


