<?php

namespace App\Http\Controllers;

use App\Models\Visit;
use App\Support\Tracker;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Sayfa görüntüleme: sayfa tarayıcıda gerçekten açılınca JavaScript bildirir (resources/js/track.js).
 * Botların çoğu JavaScript çalıştırmadığı için sayılmaz; ayrıca bot imzası, otomasyon tarayıcısı (webdriver),
 * dil başlığı olmayan istekler elenir ve IP başına hız sınırı vardır (routes/web.php).
 */
class TrackViewController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'url' => ['required', 'string', 'max:1000'],
            'ref' => ['nullable', 'string', 'max:1000'],
            'wd' => ['nullable', 'in:0,1'],
        ]);
        if (Tracker::isBot($request) || ($data['wd'] ?? '0') === '1') {
            return response()->noContent();
        }

        $path = '/' . ltrim((string) parse_url($data['url'], PHP_URL_PATH), '/');
        parse_str((string) parse_url($data['url'], PHP_URL_QUERY), $query);
        $segments = array_values(array_filter(explode('/', $path), 'strlen'));
        $locale = array_key_exists($segments[0] ?? '', config('laravellocalization.supportedLocales', [])) ? $segments[0] : null;

        $referrer = strtolower((string) parse_url((string) ($data['ref'] ?? ''), PHP_URL_HOST));
        $referrer = preg_replace('/^www\./', '', $referrer);
        if (Tracker::isSpamReferrer($referrer)) {
            return response()->noContent();
        }
        $ownHost = preg_replace('/^www\./', '', strtolower($request->getHost()));
        $utm = fn (string $key) => is_string($query[$key] ?? null) && trim($query[$key]) !== '' ? Str::limit(strtolower(trim($query[$key])), 100, '') : null;

        [$visitor, $session] = Tracker::ids($request);

        Visit::query()->insert([
            'visitor_id' => $visitor,
            'session_id' => $session,
            'path' => Str::limit($path, 500, ''),
            'page_type' => Tracker::pageTypeFromPath($path),
            'page_id' => null,
            // Kendi sitemizden gelinen sayfalar kaynak sayılmaz
            'referrer_host' => $referrer && $referrer !== $ownHost ? Str::limit($referrer, 190, '') : null,
            'utm_source' => $utm('utm_source'),
            'utm_medium' => $utm('utm_medium'),
            'utm_campaign' => $utm('utm_campaign'),
            ...Tracker::context($request, $locale),
            'created_at' => Tracker::now(),
        ]);

        $response = response()->noContent();
        Tracker::remember($response, $visitor, $session);

        return $response;
    }
}
