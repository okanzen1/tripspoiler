<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Activity;
use App\Models\Blog;
use Illuminate\Http\Request;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $query = trim($request->get('q', ''));

        if (mb_strlen($query) < 2) {
            return response()->json(['search' => []]);
        }

        /*
        |--------------------------------------------------------------------------
        | Locale
        |--------------------------------------------------------------------------
        */

        $locale = LaravelLocalization::getCurrentLocale();

        /*
        |--------------------------------------------------------------------------
        | Query sanitize
        |--------------------------------------------------------------------------
        */

        $query = mb_strtolower($query);
        $query = preg_replace('/[^\p{L}\p{N}\s]/u', '', $query);

        $words = array_filter(
            preg_split('/\s+/', $query),
            fn($word) => mb_strlen($word) >= 2
        );

        if (empty($words)) {
            return response()->json(['search' => []]);
        }

        /*
        |--------------------------------------------------------------------------
        | Cities
        |--------------------------------------------------------------------------
        */

        $cities = City::with('country')
            ->where('active', true)
            ->whereHas('country', fn($q) => $q->where('active', true))
            ->where(function ($q) use ($words, $locale) {

                foreach ($words as $word) {

                    $q->where(function ($sub) use ($word, $locale) {

                        $sub->whereRaw(
                            "LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.\"{$locale}\"'))) LIKE ?",
                            ["%{$word}%"]
                        )
                        ->orWhereRaw(
                            "LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.\"en\"'))) LIKE ?",
                            ["%{$word}%"]
                        );

                    });

                }

            })
            ->limit(10)
            ->get()
            ->map(function ($city) use ($locale) {

                $slug = $city->getTranslation('slug', $locale)
                    ?? $city->getTranslation('slug', 'en');

                return [
                    'type' => 'city',
                    'name' => $city->getTranslation('name', $locale)
                        ?? $city->getTranslation('name', 'en'),
                    'country' => $city->country?->getTranslation('name', $locale)
                        ?? $city->country?->getTranslation('name', 'en'),
                    'url' => route('cities.show', ['slug' => $slug]),
                ];

            });

        /*
        |--------------------------------------------------------------------------
        | Activities
        |--------------------------------------------------------------------------
        */

        $activities = Activity::with('city.country')
            ->where('status', true)
            ->where(function ($q) use ($words, $locale) {

                foreach ($words as $word) {

                    $q->where(function ($sub) use ($word, $locale) {

                        $sub->whereRaw(
                            "LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.\"{$locale}\"'))) LIKE ?",
                            ["%{$word}%"]
                        )
                        ->orWhereRaw(
                            "LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.\"en\"'))) LIKE ?",
                            ["%{$word}%"]
                        );

                    });

                }

            })
            ->limit(10)
            ->get()
            ->map(function ($activity) use ($locale) {

                $slug = $activity->getTranslation('slug', $locale)
                    ?? $activity->getTranslation('slug', 'en');

                return [
                    'type' => 'activity',
                    'name' => $activity->getTranslation('name', $locale)
                        ?? $activity->getTranslation('name', 'en'),
                    'country' => $activity->city?->country?->getTranslation('name', $locale)
                        ?? $activity->city?->country?->getTranslation('name', 'en'),
                    'url' => route('activities.show', ['slug' => $slug]),
                ];

            });

        /*
        |--------------------------------------------------------------------------
        | Blogs
        |--------------------------------------------------------------------------
        */

        $blogs = Blog::with('city.country')
            ->where('status', true)
            ->where(function ($q) use ($words, $locale) {

                foreach ($words as $word) {

                    $q->where(function ($sub) use ($word, $locale) {

                        $sub->whereRaw(
                            "LOWER(JSON_UNQUOTE(JSON_EXTRACT(title, '$.\"{$locale}\"'))) LIKE ?",
                            ["%{$word}%"]
                        )
                        ->orWhereRaw(
                            "LOWER(JSON_UNQUOTE(JSON_EXTRACT(title, '$.\"en\"'))) LIKE ?",
                            ["%{$word}%"]
                        );

                    });

                }

            })
            ->limit(10)
            ->get()
            ->map(function ($blog) use ($locale) {

                $slug = $blog->getTranslation('slug', $locale)
                    ?? $blog->getTranslation('slug', 'en');

                return [
                    'type' => 'blog',
                    'name' => $blog->getTranslation('title', $locale)
                        ?? $blog->getTranslation('title', 'en'),
                    'country' => $blog->city?->country?->getTranslation('name', $locale)
                        ?? $blog->city?->country?->getTranslation('name', 'en'),
                    'url' => route('blog.show', ['slug' => $slug]),
                ];

            });

        /*
        |--------------------------------------------------------------------------
        | Merge Results
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'search' => collect()
                ->merge($cities)
                ->merge($activities)
                ->merge($blogs)
                ->values()
        ]);
    }
}