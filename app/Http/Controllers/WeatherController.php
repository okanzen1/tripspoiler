<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\City;
use App\Models\PageBanner;
use App\Support\Weather;
use Illuminate\Http\Request;

/**
 * Hava durumu sayfası (/weather?city=istanbul): seçili şehrin detaylı tahmini (saatlik, 7 gün, gün doğumu/batımı,
 * ne giysem, bu havada ne yapılır) ve altta bütün şehirlerin listesi (arama + ülke filtresi; tek ülke varsa filtre yok).
 * Şehir: adresteki ?city=, yoksa sitede seçili şehir (session "selected_city_id"), yoksa listedeki ilk şehir.
 * Banner: Sayfalar › Hava durumu › Banner'da resim varsa o (şehre özel de olabilir), yoksa şehrin kendi banner'ı.
 */
class WeatherController extends Controller
{
    public function index(Request $request)
    {
        $cities = City::visible()->with('country')->whereNotNull('latitude')->orderBy('name')->get();

        try {
            $forecasts = Weather::many($cities);
        } catch (\Throwable $e) {
            report($e);
            $forecasts = [];
        }
        $cities = $cities->filter(fn (City $c) => !empty($forecasts[$c->id]))->values();

        $requested = $cities->firstWhere('slug', (string) $request->query('city'));
        $city = $requested
            ?? $cities->firstWhere('id', (int) session('selected_city_id'))
            ?? $cities->first();
        // Burada seçilen şehir aktiviteler, şehirler ve blogda da seçili olur (ve tersi)
        if ($requested) {
            session(['selected_city_id' => $requested->id]);
        }

        $suggestions = collect();
        $banner = null;
        if ($city) {
            $wet = Weather::isWet($forecasts[$city->id]['days']);
            try {
                $suggestions = Activity::visible()->with('cover')->where('city_id', $city->id)
                    ->whereIn('setting', $wet ? ['indoor', 'both'] : ['outdoor', 'both'])
                    ->orderByDesc('is_popular')->orderBy('sort_order')->take(4)->get();
            } catch (\Throwable) {
                // panelde migrate henüz çalışmadıysa (setting yok)
            }
            $hasOwn = PageBanner::where('page_key', 'weather')->where(fn ($q) => $q->whereNull('city_id')->orWhere('city_id', $city->id))->whereNotNull('image')->exists();
            $banner = PageBanner::urls($hasOwn ? 'weather' : 'cities', $city->id);
        }

        return view('other.weather', [
            'cities' => $cities,
            'forecasts' => $forecasts,
            'city' => $city,
            'forecast' => $city ? $forecasts[$city->id] : null,
            'banner' => $banner,
            'suggestions' => $suggestions,
            'countries' => $cities->pluck('country')->filter()->unique('id')->sortBy('name')->values(),
        ]);
    }
}
