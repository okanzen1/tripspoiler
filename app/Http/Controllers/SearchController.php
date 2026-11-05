<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Blog;
use App\Models\City;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $locale = LaravelLocalization::getCurrentLocale();
        $words = $this->words((string) $request->get('q', ''));

        if (empty($words)) {
            return response()->json(['search' => []]);
        }

        /*
        |--------------------------------------------------------------------------
        | Cities
        |--------------------------------------------------------------------------
        */

        // Sadece rehberi olan şehirler (boş şehir sayfasına götürmesin)
        $cities = City::with('country')
            ->visible()
            ->whereIn('id', CityController::guideCityIds())
            ->where(fn ($q) => $this->matchAll($q, 'name', null, $words))
            ->limit(10)
            ->get()
            ->map(fn ($city) => [
                'type' => 'city',
                'name' => $city->name,
                'subtitle' => $city->country?->name,
                'image' => null,
                'url' => route('cities.show', ['slug' => $city->slug]),
            ]);

        /*
        |--------------------------------------------------------------------------
        | Activities
        |--------------------------------------------------------------------------
        */

        $activities = Activity::with(['city.country', 'cover'])
            ->visible()
            ->where(fn ($q) => $this->matchAll($q, 'name', $locale, $words))
            ->orderByDesc('is_popular')
            ->orderBy('sort_order')
            ->limit(10)
            ->get()
            ->map(fn ($activity) => [
                'type' => 'activity',
                'name' => $activity->name,
                'subtitle' => collect([$activity->city?->name, $activity->city?->country?->name])->filter()->join(', '),
                'image' => $activity->cover?->url('sm'),
                'url' => route('activities.show', ['slug' => $activity->slug]),
            ]);

        /*
        |--------------------------------------------------------------------------
        | Blogs
        |--------------------------------------------------------------------------
        */

        $blogs = Blog::with('city.country')
            ->published()
            ->where(fn ($q) => $this->matchAll($q, 'title', $locale, $words))
            ->latest('published_at')
            ->limit(10)
            ->get()
            ->map(fn ($blog) => [
                'type' => 'blog',
                'name' => $blog->title,
                'subtitle' => __('home-hero.guide_minutes', ['count' => $blog->readingMinutes()]) . ' · ' . __('home-hero.guide_unit'),
                'image' => $blog->coverUrl('sm'),
                'url' => route('blog.show', ['slug' => $blog->slug]),
            ]);

        return response()->json([
            'search' => collect()
                ->merge($cities)
                ->merge($activities)
                ->merge($blogs)
                ->values(),
        ]);
    }

    /**
     * Arama kelimeleri: küçük harf, noktalama yok, en az 2 harf. "ı" → "i" (Topkapı = topkapi).
     *
     * @return list<string>
     */
    private function words(string $query): array
    {
        $query = str_replace('ı', 'i', mb_strtolower(trim($query)));
        $query = preg_replace('/[^\p{L}\p{N}\s]/u', '', $query);

        return array_values(array_filter(
            preg_split('/\s+/', $query),
            fn ($word) => mb_strlen($word) >= 2
        ));
    }

    /**
     * Her kelime alanda geçmeli. Büyük/küçük harf ve aksan duyarsız (Boğaz = bogaz, Dolmabahçe = dolmabahce).
     * $locale verilirse JSON alanın o dildeki ve İngilizce hali aranır; null ise düz sütun.
     */
    private function matchAll(Builder $query, string $column, ?string $locale, array $words): void
    {
        $fields = $locale === null
            ? [$column]
            : array_unique([
                "JSON_UNQUOTE(JSON_EXTRACT({$column}, '$.\"{$locale}\"'))",
                "JSON_UNQUOTE(JSON_EXTRACT({$column}, '$.\"en\"'))",
            ]);

        foreach ($words as $word) {
            $query->where(function ($sub) use ($fields, $word) {
                foreach ($fields as $field) {
                    $sub->orWhereRaw(
                        "REPLACE(LOWER({$field}), 'ı', 'i') COLLATE utf8mb4_0900_ai_ci LIKE ?",
                        ["%{$word}%"]
                    );
                }
            });
        }
    }
}
