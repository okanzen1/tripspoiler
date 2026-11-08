<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ziyaretçi takibi için ortak bilgiler (panel > Analitik bunları okur).
 * Kişisel veri tutulmaz: ziyaretçi ve oturum, tarayıcıdaki rastgele kimliklerdir (IP, isim saklanmaz).
 *   ts_vid  ziyaretçi, 1 yıl     ts_sid  oturum, son hareketten 30 dk sonra biter
 */
class Tracker
{
    public const VISITOR_COOKIE = 'ts_vid';

    public const SESSION_COOKIE = 'ts_sid';

    private const BOTS = '/bot|crawl|spider|slurp|preview|facebookexternalhit|embedly|headless|lighthouse|pagespeed|gtmetrix|pingdom|uptime|monitor|curl|wget|python|httpclient|okhttp|go-http|java\/|axios|node-fetch|scrapy|semrush|ahrefs|mj12|dotbot|petalbot|bytespider|gptbot|claudebot|ccbot/i';

    public static function isBot(Request $request): bool
    {
        $agent = (string) $request->userAgent();

        return $agent === '' || preg_match(self::BOTS, $agent) === 1;
    }

    /** Tarayıcının arka planda önceden yüklediği sayfalar sayılmaz */
    public static function isPrefetch(Request $request): bool
    {
        return str_contains(strtolower((string) ($request->header('Sec-Purpose') ?? $request->header('Purpose') ?? $request->header('X-Moz'))), 'prefetch');
    }

    /** @return array{0: string, 1: string} [ziyaretçi, oturum] — yoksa yeni üretilir */
    public static function ids(Request $request): array
    {
        $visitor = $request->cookie(self::VISITOR_COOKIE);
        $session = $request->cookie(self::SESSION_COOKIE);

        return [
            is_string($visitor) && strlen($visitor) === 32 ? $visitor : Str::random(32),
            is_string($session) && strlen($session) === 32 ? $session : Str::random(32),
        ];
    }

    /** Kimlikleri cevaba yazar; oturum her sayfada 30 dk uzar */
    public static function remember(Response $response, string $visitor, string $session): void
    {
        $secure = request()->isSecure();
        $response->headers->setCookie(Cookie::create(self::VISITOR_COOKIE, $visitor, now()->addYear(), '/', null, $secure, true, false, Cookie::SAMESITE_LAX));
        $response->headers->setCookie(Cookie::create(self::SESSION_COOKIE, $session, now()->addMinutes(30), '/', null, $secure, true, false, Cookie::SAMESITE_LAX));
    }

    /** Ortak alanlar: ülke (Cloudflare varsa), cihaz, dil */
    public static function context(Request $request, ?string $locale = null): array
    {
        $country = strtoupper((string) $request->header('CF-IPCountry'));

        return [
            'country' => preg_match('/^[A-Z]{2}$/', $country) && !in_array($country, ['XX', 'T1'], true) ? $country : null,
            'device' => self::device((string) $request->userAgent()),
            'locale' => Str::limit($locale ?? app()->getLocale(), 5, ''),
        ];
    }

    public static function device(string $agent): string
    {
        return match (true) {
            (bool) preg_match('/ipad|tablet|playbook|silk|(android(?!.*mobile))/i', $agent) => 'tablet',
            (bool) preg_match('/mobi|iphone|ipod|android|blackberry|opera mini|iemobile/i', $agent) => 'mobile',
            default => 'desktop',
        };
    }

    /** Adresten sayfa türü: "/tr/activities/x" → activity (dil öneki atlanır) */
    public static function pageTypeFromPath(string $path): string
    {
        $segments = array_values(array_filter(explode('/', $path), 'strlen'));
        if ($segments && array_key_exists($segments[0], config('laravellocalization.supportedLocales', []))) {
            array_shift($segments);
        }

        return match ($segments[0] ?? '') {
            '' => 'home',
            'cities' => 'city',
            'activities' => 'activity',
            'blog' => 'blog',
            default => 'other',
        };
    }

    /** Sayfa türü (panelde "En çok görüntülenen sayfalar") */
    public static function pageType(?string $route): string
    {
        return match (true) {
            $route === 'home' => 'home',
            str_starts_with((string) $route, 'cities.') => 'city',
            str_starts_with((string) $route, 'activities.') => 'activity',
            str_starts_with((string) $route, 'blog.') => 'blog',
            default => 'other',
        };
    }
}
