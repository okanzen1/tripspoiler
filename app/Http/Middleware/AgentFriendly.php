<?php

namespace App\Http\Middleware;

use App\Support\HtmlToMarkdown;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Yapay zekâ ajanları ve tarayıcılar için (Hostinger "AI görünürlüğü" önerileri):
 *  - Link başlığı: llms.txt (site özeti), site haritası ve sayfanın Markdown hali (RFC 8288)
 *  - "Accept: text/markdown" isteyene sayfanın ana içeriği Markdown olarak döner (HTML'den çok daha az token)
 *  - HTML'de iki etiket arasındaki boşluklar sıkıştırılır (etiket içleri ve metinler, script / style / pre /
 *    textarea olduğu gibi kalır: Alpine kodundaki satır sonları ve yorumlar bozulmasın)
 */
class AgentFriendly
{
    private const PROTECTED = '#<(script|style|pre|textarea)\b[^>]*>.*?</\1>#is';

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (!in_array($request->getMethod(), ['GET', 'HEAD'], true) || $response->getStatusCode() !== 200
            || !str_starts_with((string) $response->headers->get('Content-Type'), 'text/html')) {
            return $response;
        }

        $base = rtrim(config('app.url'), '/');
        $self = $request->url();
        $response->headers->set('Link', implode(', ', [
            '<' . $base . '/llms.txt>; rel="describedby"; type="text/plain"',
            '<' . $base . '/sitemap.xml>; rel="sitemap"; type="application/xml"',
            '<' . $self . '>; rel="alternate"; type="text/markdown"',
        ]));
        $response->headers->set('Vary', trim(implode(', ', array_filter([$response->headers->get('Vary'), 'Accept'])), ', '));

        // HEAD isteğinde gövde yok: sadece başlıklar
        if (!is_string($content = $response->getContent()) || $content === '') {
            return $response;
        }

        if ($this->wantsMarkdown($request)) {
            return response(HtmlToMarkdown::page($content, $self), 200, [
                'Content-Type' => 'text/markdown; charset=UTF-8',
                'Vary' => 'Accept',
                'Link' => $response->headers->get('Link'),
                'X-Robots-Tag' => 'noindex',
            ]);
        }

        $response->setContent($this->minify($content));

        return $response;
    }

    private function wantsMarkdown(Request $request): bool
    {
        $accept = strtolower((string) $request->header('Accept'));

        return str_contains($accept, 'text/markdown')
            && (!str_contains($accept, 'text/html') || strpos($accept, 'text/markdown') < strpos($accept, 'text/html'));
    }

    /** Yalnızca iki etiket arasındaki boşluğu (">   <") tek boşluğa indirir; korunan bloklara dokunmaz */
    private function minify(string $original): string
    {
        $blocks = [];
        $html = preg_replace_callback(self::PROTECTED, function ($m) use (&$blocks) {
            $blocks[] = $m[0];

            return "\x00" . (count($blocks) - 1) . "\x00";
        }, $original);
        if ($html === null) {
            return $original;
        }

        $html = preg_replace('/>\s{2,}</u', '> <', $html);
        if ($html === null) {
            return $original;
        }

        return preg_replace_callback("/\x00(\d+)\x00/", fn ($m) => $blocks[(int) $m[1]], $html) ?? $original;
    }
}
