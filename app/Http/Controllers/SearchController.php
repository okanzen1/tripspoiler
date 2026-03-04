<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Activity;
use App\Models\Blog;
use Illuminate\Http\Request;

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
        | Locale Security
        |--------------------------------------------------------------------------
        */

        $allowedLocales = ['en','tr','de','fr','es','it'];
        $locale = app()->getLocale();

        if (!in_array($locale, $allowedLocales)) {
            $locale = 'en';
        }

        /*
        |--------------------------------------------------------------------------
        | Query sanitize
        |--------------------------------------------------------------------------
        */

        $query = mb_strtolower($query);

        // sadece harf, sayı ve boşluk bırak
        $query = preg_replace('/[^\p{L}\p{N}\s]/u', '', $query);

        $words = array_filter(
            preg_split('/\s+/', $query),
            fn($word) => mb_strlen($word) >= 2
        );

        /*
        |--------------------------------------------------------------------------
        | Cities
        |--------------------------------------------------------------------------
        */

        $cities = City::with('country')
            ->where('active', 1)
            ->whereHas('country', fn($q) => $q->where('active', 1))
            ->where(function ($q) use ($words, $locale) {

                foreach ($words as $word) {

                    $q->whereRaw(
                        "LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.\"{$locale}\"'))) LIKE ?",
                        ['%' . $word . '%']
                    );

                }

            })
            ->limit(10)
            ->get()
            ->map(function ($city) use ($locale) {

                return [
                    'type' => 'city',
                    'name' => $city->getTranslation('name', $locale),
                    'country' => $city->country->getTranslation('name', $locale),
                    'url' => route('cities.show', $city->slug),
                ];

            });

        /*
        |--------------------------------------------------------------------------
        | Activities
        |--------------------------------------------------------------------------
        */

        $activities = Activity::with('city.country')
            ->where('status', 1)
            ->where(function ($q) use ($words, $locale) {

                foreach ($words as $word) {

                    $q->whereRaw(
                        "LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.\"{$locale}\"'))) LIKE ?",
                        ['%' . $word . '%']
                    );

                }

            })
            ->limit(10)
            ->get()
            ->map(function ($activity) use ($locale) {

                return [
                    'type' => 'activity',
                    'name' => $activity->getTranslation('name', $locale),
                    'country' => $activity->city->country->getTranslation('name', $locale),
                    'url' => route('activities.show', $activity->slug),
                ];

            });

        /*
        |--------------------------------------------------------------------------
        | Blogs
        |--------------------------------------------------------------------------
        */

        $blogs = Blog::with('city.country')
            ->where('status', 1)
            ->where(function ($q) use ($words, $locale) {

                foreach ($words as $word) {

                    $q->whereRaw(
                        "LOWER(JSON_UNQUOTE(JSON_EXTRACT(title, '$.\"{$locale}\"'))) LIKE ?",
                        ['%' . $word . '%']
                    );

                }

            })
            ->limit(10)
            ->get()
            ->map(function ($blog) use ($locale) {

                return [
                    'type' => 'blog',
                    'name' => $blog->getTranslation('title', $locale),
                    'country' => $blog->city->country->getTranslation('name', $locale),
                    'url' => route('blog.show', $blog->slug),
                ];

            });

        return response()->json([
            'search' => collect()
                ->merge($cities)
                ->merge($activities)
                ->merge($blogs)
                ->values()
        ]);
    }
}