<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BlogSubscriber;

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

        // Normalize + hash
        $normalizedEmail = mb_strtolower(trim($validated['email']));
        $emailHash = hash('sha256', $normalizedEmail);

        // Exists check
        if (BlogSubscriber::where('email_hash', $emailHash)->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'This email is already subscribed.',
            ], 409);
        }

        // Save (encrypt modelde)
        BlogSubscriber::create([
            'email' => $normalizedEmail,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'You’re subscribed! We’ll notify you when a new post is published.',
        ]);
    }
}
