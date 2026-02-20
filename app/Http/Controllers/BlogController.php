<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Blog;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $locale = app()->getLocale();
        $cities = City::where('active', true)->get();
        $cityId = $request->get('city_id') ?? 1;
        $currentCityName = $cities->firstWhere('id', $cityId)->name ?? $cities->first()->name;

        $blogs = Blog::where('status', true)
            ->with('city', 'images')
            ->where('city_id', $cityId)
            ->latest()
            ->get();

        if ($request->ajax()) {
            return view('blog.partials.list', compact('blogs', 'locale'));
        }

        return view('blog.index', compact(
            'cities',
            'blogs',
            'locale',
            'cityId',
            'currentCityName'
        ));
    }

    public function show(string $slug) 
    {
        $locale = app()->getLocale();

        $blog = Blog::query()
            ->where('status', true)
            ->where("slug->{$locale}", $slug)
            ->with([
                'contents' => function ($q) {
                    $q->where('status', true)
                    ->orderBy('sort_order', 'asc');
                },
                'activities' => function ($q) {
                    $q->where('status', true)
                    ->with([
                        'city.country',
                        'images' => function ($img) {
                            $img->orderBy('sort_order')
                                ->limit(1);
                        }
                    ]);
                }
            ])
            ->firstOrFail();

        // okunma sayısı
        $blog->increment('click_count');

        /* ---------------- HERO TITLE ---------------- */

        $rawTitle = trim($blog->getTranslation('title', $locale) ?? '');
        $words = preg_split('/\s+/', $rawTitle, -1, PREG_SPLIT_NO_EMPTY);
        $count = count($words);

        $first = '';
        $second = '';
        $break = false;

        if ($count === 1) {
            $first = $words[0];
        } elseif ($count === 2) {
            $first = $words[0];
            $second = $words[1];
        } elseif ($count === 3) {
            $first = $words[0] . ' ' . $words[1];
            $second = $words[2];
            $break = true;
        } else {
            $first = implode(' ', array_slice($words, 0, 3));
            $second = implode(' ', array_slice($words, 3));
            $break = true;
        }

        /* ---------------- HERO DATA ---------------- */

        $hero = [
            'title' => [
                'first'  => $first,
                'second' => $second,
                'break'  => $break,
            ],
            'themes'  => $blog->getTranslation('themes', $locale) ?? [],
            'excerpt' => $blog->getTranslation('excerpt', $locale) ?? '',
        ];

        return view('blog.show', compact('blog', 'locale', 'hero'));
    }
}
