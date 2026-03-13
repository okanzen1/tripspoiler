<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReviewController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'email:rfc,dns', 'max:190'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['required', 'string', 'min:10', 'max:1000'],
            'source' => ['required', 'string', 'max:50', Rule::in(['home', 'city', 'activity', 'museum', 'pass'])],
            'source_id' => ['nullable', 'integer', 'min:1'],
            'website' => ['nullable', 'max:0'],
        ]);

        // BOT kontrolü
        if ($request->filled('website')) {
            return response()->json(['success' => true]);
        }

        // EMAIL normalize
        $normalizedEmail = mb_strtolower(trim($validated['email']));

        // HASH (duplicate kontrolü için)
        $emailHash = hash('sha256', $normalizedEmail);

        // DUPLICATE kontrolü
        $exists = Review::where('email_hash', $emailHash)
            ->where('source', $validated['source'])
            ->where('source_id', $validated['source_id'])
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'You already submitted a review.'
            ]);
        }

        // KAYDET
        Review::create([
            'name' => trim($validated['name']),
            'email' => $normalizedEmail,
            'email_hash' => $emailHash,
            'rating' => $validated['rating'],
            'comment' => strip_tags(trim($validated['comment'])),
            'source' => $validated['source'],
            'source_id' => $validated['source_id'] ?? null,
            'approved' => false,
        ]);

        return response()->json([
            'success' => true
        ]);
    }
}
