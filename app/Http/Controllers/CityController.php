<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Image;
use App\Models\Page;

class CityController extends Controller
{
    public function index()
    {
        $locale = app()->getLocale();

        $cityId = session('selected_city_id');

        $city = null;

        if ($cityId) {
            $city = City::where('id', $cityId)
                ->where('active', true)
                ->first();
        }

        if (!$city) {
            $city = City::where('active', true)->firstOrFail();
        }

        return redirect()->route('cities.show', [
            'slug' => $city->getTranslation('slug', $locale)
        ]);
    }

    public function show(string $slug)
    {
        $locale = app()->getLocale();

        $city = City::where('active', true)
            ->where(function ($q) use ($locale, $slug) {
                $q->where("slug->{$locale}", $slug)
                    ->orWhere("slug->en", $slug);
            })
            ->firstOrFail();
        
        session(['selected_city_id' => $city->id]);

        $cities = City::where('active', true)
            ->select('id', 'name', 'slug')
            ->orderBy("name->{$locale}")
            ->orderBy('id')
            ->get();


        $useCache = config('app.global_cache_enabled');

        if ($useCache) {

            $data = cache()->remember(
                'cities_page_full',
                now()->addHours(6),
                function () {

                    $page = Page::where('slug', 'cities')
                        ->with([
                            'contents.experienceCategories.descriptions'
                        ])
                        ->first();

                    if (!$page) {
                        return null;
                    }

                    $images = Image::where('source', 'cities_page')
                        ->where('source_id', $page->id)
                        ->orderBy('sort_order')
                        ->get();

                    return [
                        'page'   => $page,
                        'images' => $images,
                    ];
                }
            );

            $page = $data['page'] ?? null;
            $pageImages = $data['images'] ?? collect();
        } else {

            $page = Page::where('slug', 'cities')
                ->with([
                    'contents.experienceCategories.descriptions'
                ])
                ->first();

            $pageImages = $page
                ? Image::where('source', 'cities_page')
                ->where('source_id', $page->id)
                ->orderBy('sort_order')
                ->get()
                : collect();
        }

        $pageContent = $page?->contentForCity($city->id);

        $citySchema = [
            '@context' => 'https://schema.org',
            '@type' => 'TouristDestination',

            '@id' => url()->current() . '#destination',

            'name' => $city->getTranslation('name', $locale),

            'description' => __('cities.meta_description_prefix') . ' ' . $city->getTranslation('name', $locale) . '.',

            'url' => url()->current(),

            'touristType' => 'Tourists',

            'isPartOf' => [
                '@id' => config('app.url') . '#website'
            ],

            'provider' => [
                '@id' => config('app.url') . '#organization'
            ],

            'sameAs' => [
                'https://en.wikipedia.org/wiki/' . urlencode($city->getTranslation('name', 'en'))
            ]
        ];

        $breadcrumbSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                [
                    '@type' => 'ListItem',
                    'position' => 1,
                    'name' => 'Home',
                    'item' => config('app.url')
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 2,
                    'name' => 'Cities',
                    'item' => config('app.url') . '/cities'
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 3,
                    'name' => $city->getTranslation('name', $locale),
                    'item' => url()->current()
                ]
            ]
        ];

        return view('cities.show', compact(
            'city',
            'cities',
            'locale',
            'pageContent',
            'pageImages',
            'citySchema',
            'breadcrumbSchema'
        ));
    }
}
