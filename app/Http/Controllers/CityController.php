<?php

namespace App\Http\Controllers;

use App\Models\City;

class CityController extends Controller
{
    public function show(string $slug, int $id)
    {
        $locale = app()->getLocale();

        $city = City::query()
            ->where('active', true)
            ->findOrFail($id);

        $correctSlug = $city->getTranslation('slug', $locale);

        if ($slug !== $correctSlug) {
            return redirect()->route('cities.show', [
                'slug' => $correctSlug,
                'id'   => $city->id,
            ]);
        }

        return view('cities.show', compact('city'));
    }
}
