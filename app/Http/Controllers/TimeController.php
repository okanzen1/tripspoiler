<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\PageBanner;
use App\Support\LocalTime;
use App\Support\Weather;
use Illuminate\Http\Request;

/**
 * Yerel saat sayfası (/time?city=istanbul): seçili şehrin canlı saati, saat farkı hesaplayıcı (dünyadaki bütün saat dilimleri),
 * ortak saat planlayıcı ve sitedeki şehirlerin saatleri. Dış servis yok.
 * Şehir: adresteki ?city=, yoksa sitede seçili şehir (session "selected_city_id"), yoksa listedeki ilk şehir; seçim diğer sayfalarla ortak.
 * Banner: Sayfalar › Yerel saat › Hero alanı'nda resim varsa o (şehre özel de olabilir), yoksa şehrin kendi banner'ı.
 */
class TimeController extends Controller
{
    public function index(Request $request)
    {
        $cities = City::visible()->with('country')->orderBy('name')->get();

        $requested = $cities->firstWhere('slug', (string) $request->query('city'));
        $city = $requested
            ?? $cities->firstWhere('id', (int) session('selected_city_id'))
            ?? $cities->first();
        if ($requested) {
            session(['selected_city_id' => $requested->id]);
        }

        $banner = null;
        if ($city) {
            $hasOwn = PageBanner::where('page_key', 'time')->where(fn ($q) => $q->whereNull('city_id')->orWhere('city_id', $city->id))->whereNotNull('image')->exists();
            $banner = PageBanner::urls($hasOwn ? 'time' : 'cities', $city->id);
        }

        return view('other.time', [
            'cities' => $cities,
            'city' => $city,
            'facts' => $city ? LocalTime::facts($city) : null,
            'sun' => $city ? Weather::sun($city) : null,
            'zones' => LocalTime::zones(),
            'cityZones' => $cities->mapWithKeys(fn (City $c) => [$c->id => Weather::timezone($c)]),
            'banner' => $banner,
            'countries' => $cities->pluck('country')->filter()->unique('id')->sortBy('name')->values(),
        ]);
    }
}
