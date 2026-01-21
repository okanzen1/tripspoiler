<?php

namespace App\Services;

use App\Models\City;

class SearchService
{
    public function search(string $q): array
    {
        $locale = app()->getLocale();
        $q = mb_strtolower(trim($q));

        $results = collect();

        /* ---------------- CITIES ---------------- */
        $cities = City::query()
            ->where('active', true)
            ->whereRaw(
                "LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.\"{$locale}\"'))) COLLATE utf8mb4_unicode_ci LIKE ?",
                ["%{$q}%"]
            )
            ->get()
            ->map(fn($city) => [
                'title' => $city->getTranslation('name', $locale),
                'subtitle' => __('City'),
                'type' => 'city',
                'url' => route('cities.show', [
                    'slug' => $city->getTranslation('slug', $locale),
                    'id'   => $city->id,
                ]),
            ]);

        $results = $results->merge($cities);

        return $results->values()->toArray();
    }
}
