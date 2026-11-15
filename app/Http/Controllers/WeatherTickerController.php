<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Support\Weather;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Döviz kuru şeridinin sağındaki dünya hava durumu (silindir gibi dönen 3 kutu). Sayfa açıldıktan sonra arka planda yüklenir
 * (sayfayı yavaşlatmaz). Veri App\Support\Weather (MET Norway, saatte bir); hazır HTML 15 dakika saklanır.
 */
class WeatherTickerController extends Controller
{
    /** Kutuların görünümü değişince artır: sunucudaki ve tarayıcıdaki eski kopyalar kullanılmaz */
    public const VERSION = 6;

    public function index(Request $request)
    {
        $locale = array_key_exists((string) $request->query('l'), config('laravellocalization.supportedLocales', [])) ? $request->query('l') : config('app.locale');
        app()->setLocale($locale);

        $key = 'weather-ticker:' . self::VERSION . ":{$locale}";
        // bu istek dil önekinin dışında çalışır: hava durumu sayfasının adresi ziyaretçinin diliyle (/tr/weather), göreli
        $weatherUrl = '/' . $locale . route('weather', [], false);
        $html = Cache::get($key) ?? (function () use ($key, $weatherUrl) {
            $cities = \App\Support\WorldCities::cities(); // adları sitenin dilinde
            $forecasts = rescue(fn () => Weather::many($cities), [], false);
            $items = $cities->filter(fn (City $c) => !empty($forecasts[$c->id]['now']))->map(fn (City $c) => [
                'name' => $c->name,
                // her kutu kendi şehrinin hava durumu sayfasına
                'url' => $weatherUrl . '?city=' . $c->slug,
                'flag' => $c->country?->code ? implode('', array_map(fn ($ch) => mb_chr(0x1F1E6 + ord($ch) - 65), str_split(strtoupper($c->country->code)))) : '',
                'temp' => $forecasts[$c->id]['now']['temp'],
                'condition' => $forecasts[$c->id]['now']['condition'],
                'night' => $forecasts[$c->id]['now']['night'] ?? false,
            ])->values();

            $html = $items->count() >= 3 ? view('partials.weather-ticker', ['items' => $items, 'url' => $weatherUrl])->render() : '';
            // boş sonuç saklanmaz: hava servisi o an cevap vermediyse bir sonraki ziyarette yeniden denenir
            if ($html !== '') {
                Cache::put($key, $html, 900);
            }

            return $html;
        })();

        return response($html)->header('Cache-Control', $html === '' ? 'no-store' : 'public, max-age=600');
    }
}
