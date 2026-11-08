<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\AffiliateClick;
use App\Models\Partner;
use App\Support\Tracker;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Dış bağlantıya tıklama (tarayıcı arka planda bildirir: navigator.sendBeacon). Yalnızca partner bağlantıları
 * kaydedilir: bir aktivitenin affiliate linki ya da bir partnerin sitesi. Sosyal medya gibi diğer dış linkler sayılmaz.
 */
class TrackClickController extends Controller
{
    public function store(Request $request)
    {
        if (Tracker::isBot($request)) {
            return response()->noContent();
        }

        $data = $request->validate([
            'url' => ['required', 'string', 'max:2000'],
            'path' => ['nullable', 'string', 'max:500'],
        ]);
        $url = $data['url'];
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        if (!$host || !preg_match('#^https?://#i', $url)) {
            return response()->noContent();
        }

        // 1) Aktivitenin kendi partner linki  2) Partnerin sitesi (alan adı eşleşmesi)
        $activity = Activity::query()->with('partner')->where('affiliate_url', $url)->first();
        $partner = $activity?->partner?->slug ?? $this->partnerByHost($host);
        if (!$partner) {
            return response()->noContent();
        }

        [$visitor, $session] = Tracker::ids($request);
        $path = '/' . ltrim((string) parse_url((string) ($data['path'] ?? '/'), PHP_URL_PATH), '/');
        $locale = explode('/', trim($path, '/'))[0] ?? null;

        AffiliateClick::query()->insert([
            'visitor_id' => $visitor,
            'session_id' => $session,
            'partner' => Str::limit($partner, 50, ''),
            'activity_id' => $activity?->id,
            'city_id' => $activity?->city_id,
            'path' => Str::limit($path, 500, ''),
            'page_type' => Tracker::pageTypeFromPath($path),
            'target_url' => $url,
            ...Tracker::context($request, in_array($locale, array_keys(config('laravellocalization.supportedLocales', [])), true) ? $locale : null),
            'created_at' => Tracker::now(),
        ]);

        return response()->noContent();
    }

    private function partnerByHost(string $host): ?string
    {
        $host = preg_replace('/^www\./', '', $host);

        return Partner::query()->whereNotNull('website')->get(['slug', 'website'])
            ->first(function ($partner) use ($host) {
                $site = preg_replace('/^www\./', '', strtolower((string) parse_url($partner->website, PHP_URL_HOST)));

                return $site && ($host === $site || str_ends_with($host, '.' . $site));
            })?->slug;
    }
}
