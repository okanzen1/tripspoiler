<?php

namespace App\View\Components;

use App\Models\Activity;
use App\Models\City;
use Illuminate\View\Component;

class SearchModal extends Component
{
    /** Aynı sayfada birden çok arama kutusu olursa öneriler bir kez sorgulanır */
    private static ?array $suggestions = null;

    public function render()
    {
        return view('components.search-modal', ['suggestions' => self::$suggestions ??= $this->suggestions()]);
    }

    /**
     * Arama kutusu boşken gösterilenler: şehirler ve öne çıkan deneyimler.
     */
    private function suggestions(): array
    {
        return [
            // sadece rehberi olan şehirler (boş şehir sayfasına götürmesin)
            'cities' => City::visible()->whereIn('id', \App\Http\Controllers\CityController::guideCityIds())->with('country')->orderByDesc('population')->limit(6)->get()
                ->map(fn ($city) => ['name' => $city->name, 'url' => route('cities.show', $city->slug)])->all(),
            'activities' => Activity::visible()->where('is_popular', true)->with(['cover', 'city'])
                ->orderBy('sort_order')->orderBy('id')->limit(3)->get()
                ->map(fn ($activity) => [
                    'name' => $activity->name,
                    'subtitle' => $activity->city?->name,
                    'image' => $activity->cover?->url('sm'),
                    'url' => route('activities.show', $activity->slug),
                ])->all(),
        ];
    }
}
