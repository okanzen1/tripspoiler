<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\City;
use App\Models\Activity;
use App\Models\Page;

class ActivityController extends Controller
{
    public function index(Request $request)
    {
        $locale = app()->getLocale();

        $cities = City::where('active', true)->get();
        $cityId = (int) ($request->get('city_id') ?? 1);

        $currentCity = $cities->firstWhere('id', $cityId) ?? $cities->first();
        $currentCityName = $currentCity?->getTranslation('name', $locale);

        $activities = Activity::where('city_id', $cityId)
            ->with('images')
            ->where('status', true)
            ->orderBy('id', 'desc')
            ->get();

        $page = Page::where('slug', 'activities')
            ->with('contents')
            ->first();

        $pageContent = $page?->contentForCity($cityId);

        if ($request->ajax()) {
            return view('activities.partials.list', compact(
                'activities',
                'locale',
                'cityId',
                'pageContent'
            ));
        }

        return view('activities.index', compact(
            'activities',
            'cities',
            'cityId',
            'locale',
            'currentCityName',
            'pageContent'
        ));
    }

    public function show(string $slug)
    {
        $locale = app()->getLocale();
        $activity = Activity::where("slug->{$locale}", $slug)->where('status', true)->with(['images', 'city.country'])->firstOrFail();
        $name = $activity->getTranslation('name', $locale);
        $metaTitle = $activity->getTranslation('meta_title', $locale) ?: $name . ' | TripSpoiler';
        $metaDescription = $activity->getTranslation('meta_description', $locale) ?: 'Learn about ' . $name . '. What it includes, how it works, and whether it makes sense for your trip.';

        return view('activities.show', compact(
            'activity',
            'locale',
            'metaTitle',
            'metaDescription',
        ));
    }
}
