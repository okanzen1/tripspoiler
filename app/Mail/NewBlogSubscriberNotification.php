<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewBlogSubscriberNotification extends Mailable
{
    use SerializesModels;

    public $email;

    public function __construct($email)
    {
        $this->email = $email;
    }

    public function build()
    {
        return $this->subject('Yeni Blog Abonesi')
            ->view('emails.new-blog-subscriber');
    }
}