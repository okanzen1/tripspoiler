<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tarayıcı güvenlik başlıkları: MIME tahmini kapalı, site başka sitede iframe'e gömülemez (clickjacking),
 * dış sitelere yalnızca alan adı gider, kamera/mikrofon/konum kapalı. HTTPS'te HSTS.
 * CSP yok: Alpine (CDN sürümü) eval kullanır ve sayfalarda satır içi script var; CSP eklenirse ikisi de ayarlanmalı.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $headers = $response->headers;
        $headers->set('X-Content-Type-Options', 'nosniff', false);
        $headers->set('X-Frame-Options', 'SAMEORIGIN', false);
        $headers->set('Referrer-Policy', 'strict-origin-when-cross-origin', false);
        $headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), interest-cohort=()', false);
        $headers->set('Cross-Origin-Opener-Policy', 'same-origin-allow-popups', false);

        if ($request->isSecure()) {
            $headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains', false);
        }

        // Sunucu/PHP sürümünü sızdırma
        $headers->remove('X-Powered-By');

        return $response;
    }
}
