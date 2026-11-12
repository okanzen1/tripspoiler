<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * www.tripspoiler.com → tripspoiler.com (kalıcı, 301). İki adres aynı içeriği verirse Google sayfaları kopya sayar
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

        return $next($request);
    }
}
