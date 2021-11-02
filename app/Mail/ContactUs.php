<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactUs extends Mailable
{
    use Queueable, SerializesModels;

    public $subject;
    public $message;

    public function __construct($data)
    {
        $this->subject = $data['subject'];
        $this->message = $data['message'];
    }

    public function build()
    {
        return $this->markdown('emails.contactUs');
    }
}
