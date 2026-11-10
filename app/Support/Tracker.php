<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ziyaretçi takibi için ortak bilgiler (panel > Analitik bunları okur).
 * Ziyaretçi ve oturum tarayıcıdaki rastgele kimliklerdir; IP veritabanında yalnızca şifreli durur, 6 ay sonra silinir.
 *   ts_vid  ziyaretçi, 1 yıl     ts_sid  oturum, son hareketten 30 dk sonra biter
 */
class Tracker
{
    /**
     * Analitik saatleri her zaman bu saat diliminde yazılır (panel de öyle okur). Sunucunun ya da .env'nin
     * APP_TIMEZONE ayarı farklı olsa bile "son 5 dakika" ve günlük sayılar kaymasın.
     */
    public const TIMEZONE = 'Europe/Istanbul';

    public static function now(): string
    {
        return now(self::TIMEZONE)->format('Y-m-d H:i:s');
    }

    public const VISITOR_COOKIE = 'ts_vid';

    public const SESSION_COOKIE = 'ts_sid';

    private const BOTS = '/bot\b|bot\/|crawl|spider|slurp|preview|facebookexternalhit|facebookcatalog|meta-externalagent|embedly|headless|phantom|puppeteer|playwright|selenium|lighthouse|pagespeed|gtmetrix|pingdom|uptime|monitor|curl|wget|python|httpclient|okhttp|go-http|java\/|axios|node-fetch|scrapy|semrush|ahrefs|mj12|dotbot|petalbot|bytespider|gptbot|chatgpt|oai-searchbot|claudebot|claude-web|anthropic|perplexity|ccbot|amazonbot|applebot|bingpreview|yandex(bot|images)|baiduspider|duckduckbot|sogou|exabot|seznambot|whatsapp|telegrambot|discordbot|slackbot|twitterbot|linkedinbot|pinterest|skypeuripreview|vkshare|qwantify|archive\.org|ia_archiver|feedfetcher|google-inspectiontool|googleother|adsbot|mediapartners|storebot|dataforseo|serpstat|barkrowler|blexbot|megaindex|zoominfo|censys|zgrab|masscan|nmap|nuclei/i';

    /**
     * Sahte yönlendirme (referrer spam) yapan siteler: bunlardan gelen ziyaretler hiç sayılmaz.
     * Alt alan adları da engellenir (ör. "x.aocr.org").
     */
    public const SPAM_REFERRERS = ['aocr.org'];

    public static function isSpamReferrer(?string $host): bool
    {
        $host = preg_replace('/^www\./', '', strtolower((string) $host));

        foreach (self::SPAM_REFERRERS as $spam) {
            if ($host === $spam || str_ends_with($host, '.' . $spam)) {
                return true;
            }
        }

        return false;
    }

    public static function isBot(Request $request): bool
    {
        $agent = (string) $request->userAgent();

        return $agent === ''
            || preg_match(self::BOTS, $agent) === 1
            || !$request->hasHeader('Accept-Language'); // gerçek tarayıcılar her zaman gönderir
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

    /** Ziyaretçi çerezlere izin verdi mi (partials/cookie-consent: "ts_consent" = all) */
    public static function consented(Request $request): bool
    {
        return $request->cookie('ts_consent') === 'all';
    }

    /**
     * Kimlikleri cevaba yazar; oturum her sayfada 30 dk uzar. İzin yoksa çerez yazılmaz, varsa silinir
     * (ziyaret yine sayılır: her sayfa yeni rastgele kimlik, panel ziyaretçiyi şifreli IP'nin parmak iziyle gruplar).
     */
    public static function remember(Response $response, string $visitor, string $session): void
    {
        if (!self::consented(request())) {
            foreach ([self::VISITOR_COOKIE, self::SESSION_COOKIE] as $name) {
                if (request()->hasCookie($name)) {
                    $response->headers->clearCookie($name, '/');
                }
            }

            return;
        }

        $secure = request()->isSecure();
        $response->headers->setCookie(Cookie::create(self::VISITOR_COOKIE, $visitor, now()->addYear(), '/', null, $secure, true, false, Cookie::SAMESITE_LAX));
        $response->headers->setCookie(Cookie::create(self::SESSION_COOKIE, $session, now()->addMinutes(30), '/', null, $secure, true, false, Cookie::SAMESITE_LAX));
    }

    /** Ziyaretçinin gerçek IP'si (Cloudflare arkasındaysa onun ilettiği) */
    public static function ip(Request $request): ?string
    {
        $cf = (string) $request->header('CF-Connecting-IP');
        if ($request->hasHeader('CF-Ray') && filter_var($cf, FILTER_VALIDATE_IP)) {
            return $cf;
        }

        return $request->ip();
    }

    /** Ortak alanlar: IP (şifreli + parmak izi, App\Support\Privacy; açık IP hiç yazılmaz), ülke ve şehir (IP'den; Cloudflare ülkesi varsa o), cihaz, tarayıcı, işletim sistemi, dil */
    public static function context(Request $request, ?string $locale = null): array
    {
        $ip = self::ip($request);
        $geo = GeoIp::lookup($ip);
        $cfCountry = strtoupper((string) $request->header('CF-IPCountry'));
        $agent = (string) $request->userAgent();

        return [
            'ip' => $ip ? \Illuminate\Support\Facades\Crypt::encryptString($ip) : null,
            'ip_hash' => Privacy::hash($ip),
            'country' => preg_match('/^[A-Z]{2}$/', $cfCountry) && !in_array($cfCountry, ['XX', 'T1'], true) ? $cfCountry : $geo['country'],
            'city' => $geo['city'],
            'device' => self::device($agent),
            'browser' => self::browser($agent),
            'os' => self::os($agent),
            'locale' => Str::limit($locale ?? app()->getLocale(), 5, ''),
        ];
    }

    public static function browser(string $agent): ?string
    {
        return match (true) {
            (bool) preg_match('/edg(e|a|ios)?\//i', $agent) => 'Edge',
            (bool) preg_match('/opr\/|opera/i', $agent) => 'Opera',
            (bool) preg_match('/samsungbrowser/i', $agent) => 'Samsung Internet',
            (bool) preg_match('/yabrowser/i', $agent) => 'Yandex',
            (bool) preg_match('/firefox|fxios/i', $agent) => 'Firefox',
            (bool) preg_match('/chrome|crios/i', $agent) => 'Chrome',
            (bool) preg_match('/safari/i', $agent) => 'Safari',
            default => null,
        };
    }

    public static function os(string $agent): ?string
    {
        return match (true) {
            (bool) preg_match('/iphone|ipad|ipod/i', $agent) => 'iOS',
            (bool) preg_match('/android/i', $agent) => 'Android',
            (bool) preg_match('/windows/i', $agent) => 'Windows',
            (bool) preg_match('/mac os x|macintosh/i', $agent) => 'macOS',
            (bool) preg_match('/cros/i', $agent) => 'ChromeOS',
            (bool) preg_match('/linux/i', $agent) => 'Linux',
            default => null,
        };
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
