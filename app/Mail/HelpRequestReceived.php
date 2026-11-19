<?php

namespace App\Mail;

use App\Models\HelpRequest;
use Illuminate\Mail\Mailable;

/** Misafire: "Talebini aldık" + seçimlerine uyan öneriler (kendi dilinde) */
class HelpRequestReceived extends Mailable
{
    public function __construct(public HelpRequest $item, public array $suggestions) {}

    public function build()
    {
        return $this->subject(__('plan-page.mail_subject'))
            ->replyTo(config('mail.contact_to') ?: config('mail.from.address'))
            ->view('emails.help-request-received');
    }
}
