<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Sayfa önbelleği: herkese aynı görünen sayfaların hazır HTML'i saklanır, sonraki ziyaretçiye veritabanına gitmeden
 * verilir (sunucu sayfayı her seferinde ~0,7 sn'de hazırlıyordu).
 *
 * - Sadece ROUTES'taki sayfalar (ziyaretçinin seçtiği şehre göre değişen liste sayfaları önbelleğe girmez).
 * - Formların güvenlik kodu (CSRF) saklanmaz: hazır sayfa verilirken her ziyaretçinin kendi koduyla değiştirilir.
 * - Panelde bir içerik kaydedilince panel Setting page_cache_version'ı değiştirir: bütün hazır sayfalar eskimiş sayılır.
 *   Ayrıca en geç TTL sonra yeniden hazırlanır.
 * - Flaş mesajı / form hatası olan ziyaretçiye hazır sayfa verilmez ve onun sayfası saklanmaz.
 */
class PageCache
{
    /** Önbelleğe giren sayfalar (route adı) */
    private const ROUTES = ['home', 'activities.show', 'attractions.show', 'blog.show', 'cities.show', 'about', 'privacy'];

    /** Adreste bulunsa da sayfayı değiştirmeyen parametreler (reklam / paylaşım izleri) */
    private const IGNORED_QUERY = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'fbclid', 'gclid', 'msclkid', 'ref'];

    private const TTL = 1800;

    private const TOKEN = '%%PAGE-CACHE-CSRF%%';

    public function handle(Request $request, Closure $next): Response
    {
        $key = $this->key($request);
        if ($key === null) {
            return $next($request);
        }

        $request->attributes->set('page_cacheable', true);
        $version = (string) (Setting::many(['page_cache_version'])['page_cache_version'] ?? '0');
        $store = Cache::store('file');
        $hit = rescue(fn () => $store->get($key), null, false);

        if (is_array($hit) && ($hit['version'] ?? null) === $version && is_string($html = @gzdecode($hit['html'] ?? ''))) {
            // Sayfanın kendi oturum etkisi (ör. şehir rehberi açılınca aktiviteler o şehirle açılır)
            foreach ($hit['session'] ?? [] as $name => $value) {
                if ($name !== 'selected_city_id' || !session('keep_selected_city')) {
                    session([$name => $value]);
                }
            }

            return response(str_replace(self::TOKEN, csrf_token(), $html), 200, [
                'Content-Type' => 'text/html; charset=utf-8',
                'X-Page-Cache' => 'hit',
            ]);
        }

        $response = $next($request);

        if ($response->getStatusCode() === 200
            && str_starts_with((string) $response->headers->get('Content-Type'), 'text/html')
            && is_string($content = $response->getContent()) && $content !== '' && strlen($content) < 3_000_000
            && !$response->headers->has('Content-Encoding')) {
            $token = csrf_token();
            rescue(fn () => $store->put($key, [
                'version' => $version,
                'html' => gzencode($token ? str_replace($token, self::TOKEN, $content) : $content, 6),
                'session' => (array) $request->attributes->get('page_cache_session', []),
            ], self::TTL), null, false);
            $response->headers->set('X-Page-Cache', 'miss');
        }

        return $response;
    }

    /** Önbellek anahtarı; bu istek önbelleğe uygun değilse null */
    private function key(Request $request): ?string
    {
        if (!in_array($request->getMethod(), ['GET', 'HEAD'], true)
            || !in_array($request->route()?->getName(), self::ROUTES, true)
            || $request->hasSession() && ($request->session()->has('errors') || $request->session()->get('_flash.old', []) !== [])) {
            return null;
        }
        $query = array_diff_key($request->query(), array_flip(self::IGNORED_QUERY));
        if ($query !== []) {
            return null; // ?city=… gibi parametreli adresler her seferinde hazırlanır
        }
        try {
            return 'page:' . sha1($request->getSchemeAndHttpHost() . $request->getPathInfo());
        } catch (Throwable) {
            return null;
        }
    }
}
