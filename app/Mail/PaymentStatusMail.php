<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;

use Illuminate\Queue\SerializesModels;

class PaymentStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function build(): self
    {
        $subject = $this->data['status'] === 'approved' ? 'Payment Approved' : 'Payment Update';
        return $this->subject($subject)
            ->view('emails.payments.status')
            ->with($this->data);
    }
}


