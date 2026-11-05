<?php

namespace App\Http\Controllers;

use App\Mail\ContactMessageMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function submit(Request $request)
    {
        // Honeypot — botlar doldurur
        if ($request->filled('website')) {
            abort(403);
        }

        // Validation
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email|max:180',
            'message' => 'required|string|max:3000',
            'topic' => 'nullable|in:general,booking,partner,bug',
        ]);

        // Alıcı mail — .env üzerinden
        $toEmail = config('mail.contact_to') ?? env('MAIL_CONTACT_TO');

        if (!$toEmail) {
            abort(500, 'Contact email not configured.');
        }

        // Mail gönder
        Mail::to($toEmail)->send(
            new ContactMessageMail(
                $data['name'],
                $data['email'],
                // konu mesajın başına (panelde e-postada görünür)
                ($data['topic'] ?? null ? '[' . __('contact-page.topic_' . $data['topic'], [], 'en') . "]\n\n" : '') . $data['message']
            )
        );

        // Sayfa yenilenmeden gönderim (fetch) JSON alır; JS kapalıysa eski yöntem
        return $request->expectsJson() ? response()->json(['ok' => true]) : back()->with('contact_sent', true);
    }
}
