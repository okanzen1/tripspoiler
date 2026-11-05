<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BlogSubscriber;
use Illuminate\Support\Facades\Mail;
use App\Mail\NewBlogSubscriberNotification;

class NewsletterSubscribeController extends Controller
{
    public function store(Request $request)
    {
        // Honeypot
        if ($request->filled('website')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Bot detected.',
            ], 422);
        }

        // Validation
        $validated = $request->validate([
            'email' => 'required|email:rfc,dns|max:255',
        ]);

        $normalizedEmail = mb_strtolower(trim($validated['email']));
        $subscriber = BlogSubscriber::where('email_hash', BlogSubscriber::hash($normalizedEmail))->first();

        if ($subscriber && !$subscriber->unsubscribed_at) {
            return response()->json([
                'status' => 'error',
                'message' => 'This email is already subscribed.',
            ], 409);
        }

        // Daha önce abonelikten çıktıysa yeniden aktif olur
        $subscriber ??= new BlogSubscriber(['email' => $normalizedEmail]);
        $subscriber->fill([
            'unsubscribed_at' => null,
            'source_path' => mb_substr((string) parse_url((string) $request->headers->get('referer'), PHP_URL_PATH), 0, 500) ?: null,
        ])->save();

        if (app()->environment('production')) {
            Mail::to('info@tripspoiler.com')
                ->send(new NewBlogSubscriberNotification($normalizedEmail));
        }

        return response()->json([
            'status' => 'success',
            'message' => 'You’re subscribed! We’ll notify you when a new post is published.',
        ]);
    }
}
