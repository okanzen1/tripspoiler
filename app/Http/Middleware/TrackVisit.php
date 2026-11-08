<?php

namespace App\Http\Middleware;

use App\Models\Visit;
use App\Support\Tracker;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

use function Illuminate\Support\defer;

/**
 * Her gerçek sayfa görüntülemesini "visits" tablosuna yazar (panel > Analitik).
 * Sayılmayanlar: botlar, önceden yükleme, AJAX/JSON, yönlendirme ve hata sayfaları, görseller.
 * Yazma, cevap gönderildikten sonra yapılır: sayfa yavaşlamaz.
 */
class TrackVisit
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (!$this->shouldTrack($request, $response)) {
            return $response;
        }

        [$visitor, $session] = Tracker::ids($request);
        Tracker::remember($response, $visitor, $session);

        $referrer = parse_url((string) $request->headers->get('referer'), PHP_URL_HOST);
        $row = [
            'visitor_id' => $visitor,
            'session_id' => $session,
            'path' => Str::limit('/' . ltrim($request->path(), '/'), 500, ''),
            'page_type' => Tracker::pageType($request->route()?->getName()),
            'page_id' => null,
            // Kendi sitemizden gelinen sayfalar kaynak sayılmaz
            'referrer_host' => $referrer && $referrer !== $request->getHost() ? Str::limit(preg_replace('/^www\./', '', strtolower($referrer)), 190, '') : null,
            'utm_source' => $this->utm($request, 'utm_source'),
            'utm_medium' => $this->utm($request, 'utm_medium'),
            'utm_campaign' => $this->utm($request, 'utm_campaign'),
            ...Tracker::context($request),
            'created_at' => now(),
        ];

        defer(function () use ($row) {
            try {
                Visit::query()->insert($row);
            } catch (\Throwable $e) {
                report($e); // takip hatası siteyi asla bozmasın
            }
        });

        return $response;
    }

    private function shouldTrack(Request $request, Response $response): bool
    {
        return $request->isMethod('GET')
            && $response->getStatusCode() === 200
            && str_contains((string) $response->headers->get('Content-Type'), 'text/html')
            && !$request->ajax() && !$request->expectsJson()
            && !Tracker::isPrefetch($request)
            && !Tracker::isBot($request);
    }

    private function utm(Request $request, string $key): ?string
    {
        $value = trim((string) $request->query($key));

        return $value === '' ? null : Str::limit(strtolower($value), 100, '');
    }
}
