<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Route;

/**
 * Sayfa bölümleri (anasayfa hariç): panelde her sayfa için sıra ve aç/kapa (Setting "page_sections_{sayfa}").
 * Değer: {"order": ["reviews", …], "on": ["reviews", "partners", …]}. Kayıt yoksa DEFAULTS kullanılır.
 * Panel de aynı listeyi kullanır (tripspoiler-admin/app/Support/PageSections.php).
 * Tüm sitede kapatılan bölüm (SiteSections) burada açık olsa da görünmez.
 */
class PageSections
{
    /** Sırası değiştirilebilen içerik bölümleri */
    public const CONTENT = ['popular', 'blog', 'city_entry', 'faq', 'reviews', 'social', 'steps', 'intro'];

    /** Sayfanın ana içeriği de sıralamaya girer (her zaman açık): sayfa => anahtar "main" */
    public const MAIN = ['activities', 'activity', 'reviews'];

    /** Yeri sabit, sadece aç/kapa */
    public const FIXED = ['announcements', 'partners'];

    /** Panelde ayar yapılmamışsa açık olan içerik bölümleri (sırasıyla) */
    public const DEFAULTS = [
        'activities' => ['reviews', 'social'],
        // Aktivite detayı: bölümler o aktiviteye göre dolar (yorumları, soruları, aynı şehirdeki benzerleri)
        'activity' => ['reviews', 'faq'],
        'blog' => ['popular', 'reviews', 'social'],
        'cities' => ['popular', 'blog', 'reviews'],
        'reviews' => ['social'],
        'about' => [],
        'contact' => [],
        'privacy' => [],
    ];

    /** Route adı → sayfa anahtarı */
    public const ROUTES = [
        'activities.index' => 'activities',
        'activities.show' => 'activity',
        'blog.index' => 'blog',
        'cities.show' => 'cities',
        'reviews.index' => 'reviews',
        'about' => 'about',
        'contact' => 'contact',
        'privacy' => 'privacy',
    ];

    private static array $cache = [];

    /** @return array{order: list<string>, on: list<string>} */
    public static function config(string $page): array
    {
        if (isset(self::$cache[$page])) {
            return self::$cache[$page];
        }

        $saved = json_decode((string) Setting::many(["page_sections_{$page}"])["page_sections_{$page}"], true);
        $defaults = self::DEFAULTS[$page] ?? [];

        $main = in_array($page, self::MAIN, true) ? ['main'] : [];
        $guide = self::guideKeys($page);
        $order = array_values(array_intersect($saved['order'] ?? [], [...$main, ...self::CONTENT, ...$guide]));
        // Ana içerik varsayılan olarak en üstte; rehber alanları içerikten önce; listede olmayan (yeni eklenen) bölümler sona
        $order = array_values(array_unique([...(in_array('main', $order, true) ? [] : $main), ...$order, ...$guide, ...$defaults, ...self::CONTENT]));
        $on = is_array($saved['on'] ?? null) ? $saved['on'] : [...$defaults, ...self::FIXED];
        // Kayıtlı sırada hiç olmayan rehber alanı yenidir: açık başlar (panelle aynı kural)
        $on = array_values(array_unique([...$on, ...array_diff($guide, $saved['order'] ?? [])]));

        return self::$cache[$page] = ['order' => $order, 'on' => array_values($on)];
    }

    /**
     * Şehirler sayfasındaki rehber alanları: "guide:{topic id}" (panelde Bölümler listesinde sıralanır).
     *
     * @return list<string>
     */
    /** Sayfaya özel içerik bölümleri (panelle aynı; görünümü resources/views/sections/{anahtar}.blade.php) */
    public const EXTRA = [
        'about' => ['about_story', 'about_how', 'about_values', 'about_cta'],
        'privacy' => ['privacy_who', 'privacy_collect', 'privacy_use', 'privacy_cookies', 'privacy_partners', 'privacy_storage', 'privacy_rights', 'privacy_changes'],
    ];

    public static function guideKeys(string $page): array
    {
        if (isset(self::EXTRA[$page])) {
            return self::EXTRA[$page];
        }
        if ($page !== 'cities') {
            return [];
        }

        return \App\Models\CityGuideTopic::orderBy('sort_order')->orderBy('id')->pluck('id')->map(fn ($id) => "guide:{$id}")->all();
    }

    /**
     * Sayfada sırasıyla gösterilecek içerik bölümleri.
     *
     * @return list<string>
     */
    public static function content(string $page): array
    {
        $config = self::config($page);

        // Şehirle ilgisi olmayan sayfalarda şehre bağlı bölümler gösterilmez (panelle aynı kural)
        $skip = in_array($page, ['contact', 'about', 'privacy', 'reviews'], true) ? ['popular', 'blog', 'city_entry'] : [];
        if ($page === 'reviews') {
            $skip[] = 'reviews'; // sayfanın kendisi yorum listesi
        }

        return array_values(array_filter($config['order'], fn ($key) => !in_array($key, $skip, true) && ($key === 'main' || (in_array($key, $config['on'], true) && SiteSections::enabled($key)))));
    }

    /**
     * Aktiviteler bağlantısı: aktiviteler sayfasındaysak listeye kaydırır (#activities-list), değilse sayfaya gider.
     */
    public static function activitiesLink(array $params = []): string
    {
        return Route::currentRouteName() === 'activities.index' ? '#activities-list' : route('activities.index', $params);
    }

    /**
     * Duyuru şeridi / partner şeridi bu sayfada açık mı? Ayarı olmayan sayfalarda (anasayfa, detaylar) her zaman açık.
     */
    public static function fixedEnabled(string $key): bool
    {
        $page = self::ROUTES[Route::currentRouteName()] ?? null;

        return $page === null || in_array($key, self::config($page)['on'], true);
    }
}
