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
        $city = City::where('active', true)->select('id', 'slug')->firstOrFail();

        return redirect()->route(
            'cities.show',
            $city->getTranslation('slug', $locale)
        );
    }

    public function show(string $slug)
    {
        $locale = app()->getLocale();

        $city = City::where('active', true)
            ->where("slug->{$locale}", $slug)
            ->firstOrFail();

        $cities = City::where('active', true)
            ->select('id', 'name', 'slug')
            ->orderBy("name->{$locale}")
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

        return view('cities.show', compact(
            'city',
            'cities',
            'locale',
            'pageContent',
            'pageImages'
        ));
    }
}
