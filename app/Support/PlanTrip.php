<?php

namespace App\Support;

use App\Models\Activity;
use App\Models\Attraction;
use App\Models\Blog;
use App\Models\City;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

/**
 * "Plan your trip" (yardım talebi) seçenekleri ve misafire giden önerileri.
 * Seçenek anahtarları panelde de aynı (tripspoiler-admin/app/Models/HelpRequest::LABELS); yazılar lang/*\/plan-page.php.
 */
class PlanTrip
{
    public const GROUPS = ['solo', 'couple', 'family', 'friends', 'business'];
    public const ACCESS = ['wheelchair', 'stroller', 'limited_walking', 'senior'];
    public const NEEDS = ['activities', 'sightseeing', 'stay', 'transport', 'food', 'daytrips', 'practical', 'occasion'];
    public const STAY_VIBE = ['quiet', 'lively', 'any'];
    public const STAY_BUDGET = ['budget', 'mid', 'luxury'];
    public const STAY_EXTRAS = ['central', 'transit', 'family', 'sea'];
    public const FOOD = ['local', 'street', 'vegetarian', 'vegan', 'halal', 'kids'];
    public const OCCASION = ['honeymoon', 'birthday', 'anniversary', 'proposal', 'other'];
    public const PACE = ['relaxed', 'balanced', 'packed'];
    public const INTERESTS = ['history', 'views', 'food', 'shopping', 'nightlife', 'nature', 'art', 'kids'];
    public const BUDGET = ['economy', 'mid', 'comfort'];

    /** Blog başlıklarında ihtiyaca göre aranan kelimeler (İngilizce başlık) */
    private const BLOG_WORDS = [
        'stay' => ['where to stay', 'stay in', 'neighbourhood', 'neighborhood'],
        'transport' => ['airport', 'transport', 'metro', 'istanbulkart', 'card', 'ferry', 'bus'],
        'food' => ['eat', 'food', 'dishes', 'street food'],
        'daytrips' => ['day trip', 'island', 'from athens', 'from istanbul'],
        'practical' => ['safe', 'budget', 'packing', 'best time', 'tips', 'etiquette', 'dress'],
        'sightseeing' => ['itinerary', 'in 2 days', '3 days', 'guide', 'viewpoint'],
        'activities' => ['pass', 'tickets', 'combo', 'worth'],
    ];

    /** Panel tablosu kurulmadan (migrate) sayfa açılmaz, menüde görünmez */
    public static function available(): bool
    {
        if (Cache::get('schema:help_requests')) {
            return true;
        }
        $has = Schema::hasTable('help_requests');
        $has ? Cache::forever('schema:help_requests', true) : Cache::put('schema:help_requests', false, now()->addMinutes(10));

        return $has;
    }

    /** Formdaki şehirler: sitede görünen ve aktivitesi olanlar (en kalabalıktan) */
    public static function cities(): Collection
    {
        return City::visible()->whereHas('activities', fn ($q) => $q->visible())
            ->with('country')->orderByDesc('population')->get(['id', 'name', 'slug', 'country_id']);
    }

    /**
     * Misafirin seçimlerine göre öneriler (şehir başına): popüler aktiviteler, gezilecek yerler, ilgili yazılar.
     *
     * @return list<array{city: City, activities: Collection, places: Collection, blogs: Collection}>
     */
    public static function suggestions(array $answers): array
    {
        $needs = (array) ($answers['needs'] ?? []);
        $out = [];

        foreach (City::visible()->whereIn('id', (array) ($answers['cities'] ?? []))->get() as $city) {
            $activities = Popularity::rank(Activity::visible()->where('city_id', $city->id)->with('cover'))->limit(3)->get();

            $places = Attraction::visible()->where('city_id', $city->id)->orderBy('sort_order')->limit(in_array('sightseeing', $needs, true) ? 4 : 2)->get();

            $words = collect($needs)->flatMap(fn ($n) => self::BLOG_WORDS[$n] ?? [])->unique()->values();
            $blogs = Blog::published()->forCity($city->id)->latest('published_at')->limit(60)->get()
                ->filter(fn ($b) => $words->contains(fn ($w) => str_contains(mb_strtolower((string) $b->getTranslation('title', 'en', false)), $w)))
                ->take(3)->values();

            $out[] = compact('city', 'activities', 'places', 'blogs');
        }

        return $out;
    }
}
