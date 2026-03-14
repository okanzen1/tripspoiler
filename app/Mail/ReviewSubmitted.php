<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReviewSubmitted extends Mailable
{
    use SerializesModels;

    public $review;

    public function __construct($review)
    {
        $this->review = $review;
    }

    public function build()
    {
        return $this->subject('Yeni yorum Gönderildi - TripSpoiler')
            ->view('emails.review-submitted');
    }
}