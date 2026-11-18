<?php

namespace App\Support;

use App\Models\Activity;
use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

/**
 * 404 yerine kalıcı yönlendirme (301), sırayla:
 *  1) Panelin yönlendirme kayıtları (redirects tablosu; slug değişince panel otomatik ekler; zincir en fazla 5 adım).
 *  2) /{dil}/sitemap.xml → /sitemap.xml.
 *  3) Eski sitenin 12 dile çevrilmiş adresleri (/{dil}/activities|blog/{eski-slug}): adresteki konu (Topkapı, Boğaz, Ayasofya…)
 *     hangi dilde yazılmış olursa olsun tanınır, aynı konudaki güncel sayfaya; eşleşme yoksa liste sayfasına gidilir.
 * Google Search Console'daki "Bulunamadı (404)" sayfaları böylece değerini yeni sayfaya aktarır.
 */
class LegacyRedirects
{
    /** Konu → adreste geçebilecek parçalar (eski sitenin 12 dili) */
    private const TOPICS = [
        'hagia_sophia' => ['hagia', 'sophia', 'sofia', 'sophie', 'ayasofya', 'aya-sofya', 'айя', 'софи', 'アヤソフィア', '圣索菲亚', '聖索菲亞', '아야', '소피아', 'آيا', 'صوفيا'],
        'topkapi' => ['topkapi', 'topkapı', 'топкап', '托普卡', 'トプカプ', '톱카프', '토프카프', 'توبكابي', 'طوب قابي'],
        'harem' => ['harem', 'гарем', '后宫', 'ハーレム', '하렘', 'الحريم'],
        'dolmabahce' => ['dolmabah', 'долмабах', '多尔玛巴赫', '多爾瑪巴赫', 'ドルマバフチェ', '돌마바흐', 'دولما'],
        'basilica_cistern' => ['cistern', 'zistern', 'citerne', 'cisterna', 'yerebatan', 'sarnic', 'sarnıç', 'цистерн', '水宫', '貯水池', '바실리카', '수조', 'صهريج'],
        'bosphorus' => ['bosphor', 'bospor', 'bosfor', 'bósforo', 'bogaz', 'boğaz', 'босфор', '博斯普鲁斯', 'ボスポラス', '보스포러스', 'البوسفور'],
        'dervish' => ['dervis', 'derviş', 'dervish', 'derviche', 'derwisch', 'sema', 'дервиш', '苦行僧', 'デルヴィーシュ', '旋舞', '데르비시', 'الدراويش'],
        'city_pass' => ['city-pass', 'citypass', 'pase', '-pass', 'pass-', '通票', 'シティパス', '시티패스', 'سيتي-باس'],
        'airport' => ['sabiha', 'airport', 'aeropuerto', 'aeroporto', 'aéroport', 'aeroport', 'flughafen', 'havaliman', 'аэропорт', '机场', '空港', '공항', 'مطار'],
        'galata' => ['galata', 'галат', '加拉塔', 'ガラタ', '갈라타', 'غلطة'],
        'maiden_tower' => ['maiden', 'kiz-kulesi', 'kız-kulesi', 'leandro', 'léandre', 'девич', '少女塔', '乙女の塔', '처녀', 'العذراء'],
        'hamam' => ['hamam', 'hammam', 'turkish-bath', 'bain-turc', 'баня', '土耳其浴', 'ハマム', '하맘', 'حمام'],
        'blue_mosque' => ['blue-mosque', 'sultanahmet', 'mosquee-bleue', 'mosquée-bleue', 'mezquita-azul', 'moschea-blu', 'голуб', '蓝色清真寺', 'ブルーモスク', '블루-모스크', 'الأزرق'],
        'acropolis' => ['acropol', 'akropol', 'parthenon', 'partenon', 'акропол', '卫城', 'アクロポリス', '아크로폴리스', 'الأكروبوليس'],
    ];

    public static function respond(Request $request): ?\Symfony\Component\HttpFoundation\Response
    {
        if (!in_array($request->getMethod(), ['GET', 'HEAD'], true)) {
            return null;
        }
        $path = '/' . trim(rawurldecode($request->getPathInfo()), '/');
        $query = $request->getQueryString() ? '?' . $request->getQueryString() : '';

        try {
            if ($to = self::fromTable($path)) {
                return redirect()->to($to . $query, 301);
            }
        } catch (\Throwable) {
            // tablo yoksa (ör. test ortamı) sessizce devam
        }

        $locales = array_keys(LaravelLocalization::getSupportedLocales());
        if (!preg_match('#^/([a-z]{2})/(.+)$#u', $path, $m) || !in_array($m[1], $locales, true)) {
            return null;
        }
        [, $locale, $rest] = $m;

        if ($rest === 'sitemap.xml') {
            return redirect()->to('/sitemap.xml', 301);
        }
        if (!preg_match('#^(activities|blog)/([^/]+)$#u', $rest, $mm)) {
            return null;
        }
        [, $section, $slug] = $mm;

        $target = self::bestMatch($section, mb_strtolower($slug));
        $url = $target
            ? '/' . $locale . '/' . $section . '/' . $target->localSlug($locale)
            : '/' . $locale . '/' . $section;

        return $url === $path ? null : redirect()->to($url . $query, 301);
    }

    /** Panel kayıtları; zincirleme yönlendirme tek adıma indirilir */
    private static function fromTable(string $path): ?string
    {
        $current = $path;
        for ($i = 0; $i < 5; $i++) {
            $row = DB::table('redirects')->where('from_path', $current)->first(['id', 'to_path']);
            if (!$row || $row->to_path === $current) {
                break;
            }
            DB::table('redirects')->where('id', $row->id)->update(['hits' => DB::raw('hits + 1'), 'last_hit_at' => now()]);
            $current = $row->to_path;
        }

        return $current !== $path ? $current : null;
    }

    /** @return list<string> */
    public static function topics(string $text): array
    {
        $text = mb_strtolower($text);
        $found = [];
        foreach (self::TOPICS as $topic => $needles) {
            foreach ($needles as $needle) {
                if (str_contains($text, $needle)) {
                    $found[] = $topic;
                    break;
                }
            }
        }

        return $found;
    }

    /** Konuları en çok örtüşen güncel kayıt; tek konulu ürün, aynı konuyu içeren komboya tercih edilir */
    public static function bestMatch(string $section, string $slug, ?iterable $candidates = null): Activity|Blog|null
    {
        $wanted = self::topics($slug);
        if ($wanted === []) {
            return null;
        }
        $candidates ??= $section === 'activities'
            ? Activity::visible()->get()
            : Blog::published()->whereNotNull('slug')->get();

        $best = null;
        $bestScore = 0;
        foreach ($candidates as $candidate) {
            $label = $section === 'activities' ? $candidate->getTranslation('name', 'en', false) : $candidate->getTranslation('title', 'en', false);
            $have = self::topics($candidate->slug . ' ' . str_replace(' ', '-', (string) $label));
            $overlap = count(array_intersect($wanted, $have));
            if ($overlap === 0) {
                continue;
            }
            $score = $overlap * 10 - count(array_diff($have, $wanted));
            if ($score > $bestScore) {
                $best = $candidate;
                $bestScore = $score;
            }
        }

        return $best;
    }
}
