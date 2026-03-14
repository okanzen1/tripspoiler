<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReviewSubmitted;
use Illuminate\Http\Request;

use Illuminate\Validation\Rule;

class ReviewController extends Controller
{
    public function go(Request $request)
    {
        session([
            'review_source' => $request->source,
            'review_source_id' => $request->source_id
        ]);

        return redirect()->route('reviews.index');
    }

    public function index()
    {
        $source = session('review_source');
        $sourceId = session('review_source_id');
        $general = [];

        $reviews = Review::where('approved', true)
            // ->when($source, fn($q) => $q->where('source', $source))
            // ->when($sourceId, fn($q) => $q->where('source_id', $sourceId))
            ->latest()
            ->paginate(20);

        $average = round(Review::where('approved', true)->avg('rating') ?? 0, 1);
        $totalReviews = Review::where('approved', true)->count();

        $general = [
            'averageRating' => $average,
            'stars' => round($average),
            'totalReviews' => $totalReviews,
        ];

        /*
        |--------------------------------------------------------------------------
        | Review Schema
        |--------------------------------------------------------------------------
        */

        $reviewSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            '@id' => config('app.url') . '#organization',
            'name' => 'TripSpoiler',
            'url' => config('app.url'),
            'aggregateRating' => [
                '@type' => 'AggregateRating',
                'ratingValue' => $average,
                'reviewCount' => $totalReviews,
                'bestRating' => '5',
                'worstRating' => '1',
            ],
            'review' => [],
        ];

        foreach ($reviews as $review) {
            $reviewSchema['review'][] = [
                '@type' => 'Review',
                'author' => [
                    '@type' => 'Person',
                    'name' => $review->name,
                ],
                'datePublished' => $review->created_at->toIso8601String(),
                'reviewBody' => $review->comment,
                'reviewRating' => [
                    '@type' => 'Rating',
                    'ratingValue' => $review->rating,
                    'bestRating' => '5',
                    'worstRating' => '1',
                ],
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | ItemList Schema
        |--------------------------------------------------------------------------
        */

        $itemListSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            '@id' => url()->current() . '#review-list',
            'itemListElement' => [],
        ];

        foreach ($reviews as $index => $review) {
            $itemListSchema['itemListElement'][] = [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'item' => [
                    '@type' => 'Review',
                    'author' => [
                        '@type' => 'Person',
                        'name' => $review->name,
                    ],
                    'reviewBody' => $review->comment,
                    'reviewRating' => [
                        '@type' => 'Rating',
                        'ratingValue' => $review->rating,
                        'bestRating' => '5',
                        'worstRating' => '1',
                    ],
                    'datePublished' => $review->created_at->toIso8601String(),
                ],
            ];
        }

        return view('reviews.index', compact(
            'reviews',
            'source',
            'sourceId',
            'general',
            'reviewSchema',
            'itemListSchema'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'email:rfc,dns', 'max:190'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['required', 'string', 'min:10', 'max:1000'],
            'source' => ['required', 'string', 'max:50', Rule::in(['home', 'cities', 'blog', 'activity', 'activity-show', 'blog-show'])],
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
        $review = Review::create([
            'name' => trim($validated['name']),
            'email' => $normalizedEmail,
            'email_hash' => $emailHash,
            'rating' => $validated['rating'],
            'comment' => strip_tags(trim($validated['comment'])),
            'source' => $validated['source'],
            'source_id' => $validated['source_id'] ?? null,
            'approved' => false,
        ]);

        if (app()->environment('production')) {
            Mail::to('info@tripspoiler.com')->send(new ReviewSubmitted($review));
        }

        return response()->json([
            'success' => true
        ]);
    }
}
