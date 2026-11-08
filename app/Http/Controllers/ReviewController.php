<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Review;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReviewSubmitted;
use Illuminate\Http\Request;

use Illuminate\Validation\Rule;

class ReviewController extends Controller
{
    public function go(Request $request)
    {
        // Yorum formuna geri taşınır; sadece kısa bir anahtar ve sayı (bilinmeyen değer sessizce atılır, buton bozulmasın)
        $source = $request->input('source');
        $sourceId = filter_var($request->input('source_id'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        session([
            'review_source' => is_string($source) && preg_match('/^[a-z_-]{1,50}$/', $source) ? $source : null,
            'review_source_id' => $sourceId ?: null,
        ]);

        return redirect()->route('reviews.index');
    }

    public function index()
    {
        $source = session('review_source');
        $sourceId = session('review_source_id');
        $general = [];

        // yıldız süzgeci (?rating=5) ve dağılım
        $rating = in_array((int) request('rating'), [1, 2, 3, 4, 5], true) ? (int) request('rating') : null;
        $reviews = Review::approved()
            ->with('activity')
            ->when($rating, fn ($q) => $q->where('rating', $rating))
            ->latest()
            ->paginate(12)
            ->withQueryString();
        $distribution = Review::approved()->selectRaw('rating, count(*) as n')->groupBy('rating')->pluck('n', 'rating');

        $average = round(Review::where('approved', true)->avg('rating') ?? 0, 1);
        $totalReviews = Review::where('approved', true)->count();

        $general = [
            'averageRating' => $average,
            'stars' => round($average),
            'totalReviews' => $totalReviews,
            'distribution' => collect([5, 4, 3, 2, 1])->mapWithKeys(fn ($r) => [$r => (int) ($distribution[$r] ?? 0)])->all(),
            'rating' => $rating,
        ];

        /*
        |--------------------------------------------------------------------------
        | Yapısal veri
        |--------------------------------------------------------------------------
        | Yorumlar sitenin kendisi hakkında: Google, bir kuruluşun kendi sitesinde kendisi için gösterdiği
        | yorumları (self-serving) yıldız olarak göstermez ve Organization'a aggregateRating eklemeyi kural dışı sayar.
        | Bu yüzden sayfa, yorumları listeleyen bir CollectionPage olarak işaretlenir; Organization layout'tan @id ile bağlanır.
        */

        $pageUrl = \App\Support\Schema::canonical();
        $reviewSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'CollectionPage',
            '@id' => $pageUrl . '#webpage',
            'url' => $pageUrl,
            'name' => __('reviews-page.meta_title'),
            'description' => __('reviews-page.meta_description'),
            'inLanguage' => app()->getLocale(),
            'isPartOf' => ['@id' => \App\Support\Schema::websiteId()],
            'about' => \App\Support\Schema::organizationRef(),
            'mainEntity' => [
                '@type' => 'ItemList',
                'numberOfItems' => $reviews->count(),
                'itemListElement' => $reviews->values()->map(fn ($review, $i) => [
                    '@type' => 'ListItem',
                    'position' => ($reviews->firstItem() ?? 1) + $i,
                    'name' => \Illuminate\Support\Str::limit($review->comment, 110),
                ])->all(),
            ],
        ];

        return view('reviews.index', compact(
            'reviews',
            'source',
            'sourceId',
            'general',
            'reviewSchema'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'email:rfc,dns', 'max:190'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['required', 'string', 'min:10', 'max:1000'],
            'source' => ['required', 'string', 'max:50', Rule::in(['home', 'cities', 'blog', 'activity', 'activity-show', 'blog-show', 'activities', 'about', 'contact', 'privacy', 'reviews'])],
            'source_id' => ['nullable', 'integer', 'min:1'],
            'website' => ['nullable', 'max:0'],
        ]);

        // BOT kontrolü
        if ($request->filled('website')) {
            return response()->json(['success' => true]);
        }

        $normalizedEmail = mb_strtolower(trim($validated['email']));
        $emailHash = hash('sha256', $normalizedEmail);

        // Aktivite sayfasından geldiyse o aktiviteye, değilse sitenin geneline yazılır
        $activityId = in_array($validated['source'], ['activity', 'activity-show'], true) && !empty($validated['source_id'])
            ? Activity::whereKey($validated['source_id'])->value('id')
            : null;

        $exists = Review::where('email_hash', $emailHash)
            ->where('activity_id', $activityId)
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
            'activity_id' => $activityId,
            'rating' => $validated['rating'],
            'comment' => strip_tags(trim($validated['comment'])),
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
