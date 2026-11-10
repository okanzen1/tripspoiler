<?php

namespace App\Support;

use App\Models\Setting;

/**
 * Anasayfa bölümlerinin sırası: panel › Yönetim › Bölümler'de sürükle-bırak (Setting "site_settings_order",
 * panel kart anahtarları: "steps,intro-texts,blog,…"). Hero her zaman en üstte.
 * Panelde kapatılan anasayfa-özel bölümler ("home_popular", "home_faq") burada elenir; diğer bölümlerin aç/kapası
 * kendi bileşenlerinde (SiteSections).
 * Duyuru şeridi ve partner logoları sayfanın en üstünde / en altında sabittir, bu sıraya girmez.
 */
class HomeSections
{
    /** Varsayılan sıra (ayar yoksa) */
    public const DEFAULT = ['features', 'steps', 'popular', 'city_entry', 'blog', 'intro', 'social', 'reviews', 'faq'];

    /** Panel kart anahtarı → anasayfa bölümü */
    private const CARDS = [
        'features' => 'features', 'steps' => 'steps', 'city-entry-texts' => 'city_entry', 'blog' => 'blog',
        'intro-texts' => 'intro', 'social-texts' => 'social', 'reviews-texts' => 'reviews',
        'popular' => 'popular', 'faq' => 'faq',
    ];

    /** Sadece anasayfada kapatılabilenler: bölüm => SiteSections anahtarı */
    private const HOME_ONLY = ['popular' => 'home_popular', 'faq' => 'home_faq'];

    /** @return list<string> */
    public static function order(): array
    {
        $saved = array_values(array_unique(array_filter(array_map(
            fn ($card) => self::CARDS[trim($card)] ?? null,
            explode(',', (string) (Setting::many(['site_settings_order'])['site_settings_order'] ?? '')),
        ))));

        // Panelde sırası olanlar: kaydedilen sırayla; kaydedilmemiş (yeni) olanlar varsayılan yerine yakın, sonda
        $movable = array_values(array_intersect(self::DEFAULT, self::CARDS));
        $sorted = [...$saved, ...array_diff($movable, $saved)];

        // Panelde satırı olmayanlar varsayılan sıradaki öncüllerinin arkasına
        foreach (array_diff(self::DEFAULT, $movable) as $fixed) {
            $index = array_search($fixed, self::DEFAULT, true);
            $before = $index > 0 ? self::DEFAULT[$index - 1] : null;
            $at = $before !== null ? array_search($before, $sorted, true) : false;
            $at === false ? $sorted[] = $fixed : array_splice($sorted, $at + 1, 0, [$fixed]);
        }

        return array_values(array_filter($sorted, fn ($section) => !isset(self::HOME_ONLY[$section]) || SiteSections::enabled(self::HOME_ONLY[$section])));
    }
}
