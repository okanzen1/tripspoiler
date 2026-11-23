<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * www.tripspoiler.com → tripspoiler.com ve /public/… → /… (kalıcı, 301). İki adres aynı içeriği verirse Google sayfaları kopya sayar
 * ve sıralama ikiye bölünür. Yalnızca canlıda (APP_URL'in alan adı) çalışır; yerelde dokunmaz.
 */
class CanonicalHost
{
    public function handle(Request $request, Closure $next): Response
    {
        $host = parse_url((string) config('app.url'), PHP_URL_HOST);

        if ($host && $request->getHost() === 'www.' . $host) {
            return redirect()->away('https://' . $host . $request->getRequestUri(), 301);
        }

        // /public/fr/… → /fr/… : sunucuda proje klasörü de açık olduğu için "/public" önekli adres sitenin kopyasını
        // veriyordu (Bing bu adresleri dizine almıştı). Kalıcı yönlendirme: tek adres kalsın.
        $uri = $request->getRequestUri();
        if ($host && ($uri === '/public' || str_starts_with($uri, '/public/') || str_starts_with($uri, '/public?'))) {
            return redirect()->away('https://' . $host . ('/' . ltrim(substr($uri, 7), '/')), 301);
        }

        return $next($request);
    }
}
