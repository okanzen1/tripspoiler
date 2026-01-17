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

        if ($request->ajax()) {
            return view('cities.partials.list', compact('selectedCity', 'locale'));
        }

        return view('cities.index', compact(
            'cities',
            'locale',
            'cityId',
            'selectedCity'
        ));
    }


}
