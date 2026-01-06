<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactMessageMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public $name;
    public $email;
    public $body;

    public function __construct($name, $email, $body)
    {
        $this->name = $name;
        $this->email = $email;
        $this->body = $body;
    }

    public function build()
    {
        return $this->subject('New Contact Message - TripSpoiler')
                    ->replyTo($this->email)
                    ->view('emails.contact');
    }
}
