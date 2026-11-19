<?php

namespace App\Mail;

use App\Models\HelpRequest;
use Illuminate\Mail\Mailable;

/** Bize: yeni yardım talebi geldi (ayrıntısı panelde, Yardım talepleri) */
class HelpRequestNotification extends Mailable
{
    public function __construct(public HelpRequest $item) {}

    public function build()
    {
        $cities = implode(', ', (array) ($this->item->answers['city_names'] ?? []));

        return $this->subject('Yeni yardım talebi ' . $this->item->reference . ': ' . $cities)
            ->replyTo($this->item->email, $this->item->name)
            ->view('emails.help-request-notification');
    }
}
