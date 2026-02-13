<?php

namespace App\Http\Controllers;

use App\Models\City;
use Illuminate\Http\Request;

class CityController extends Controller
{
    public function index(Request $request)
    {
        $locale = app()->getLocale();
        $cities = City::where('active', true)->get();
        $cityId = $request->get('city_id') ?? $cities->first()?->id;
        $selectedCity = City::where('active', true)->find($cityId);
        $currentCityName = $selectedCity->name ?? $cities->first()->name;

        if ($request->ajax()) {
            return view('cities.partials.list', compact('selectedCity', 'locale'));
        }

        return view('cities.index', compact(
            'cities',
            'locale',
            'cityId',
            'selectedCity',
            'currentCityName'
        ));
    }

    public function show(string $slug)
    {
        $locale = app()->getLocale();

        $city = City::query()
            ->where('active', true)
            ->where("slug->{$locale}", $slug)
            ->firstOrFail();

        return view('cities.show', compact('city', 'locale'));
    }


}
