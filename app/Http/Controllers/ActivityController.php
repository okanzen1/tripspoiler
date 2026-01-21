<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\City;
use App\Models\Activity;

class ActivityController extends Controller
{
    public function index(Request $request)
    {
        $locale = app()->getLocale();
        $cities = City::where('active', true)->get();
        $cityId = $request->get('city_id') ?? 1;

        $activities = Activity::where('city_id', $cityId)
            ->where('status', true)
            ->orderBy('id', 'desc')
            ->get();

        if ($request->ajax()) {
            return view('activities.partials.list', compact('activities', 'locale','cityId'));
        }

        return view('activities.index', compact('activities', 'cities', 'cityId', 'locale'));
    }
}
