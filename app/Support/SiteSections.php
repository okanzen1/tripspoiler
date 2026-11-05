<?php

namespace App\Support;

use App\Models\Setting;

/**
 * Panelde kapatılan anasayfa bölümleri (Setting "sections_disabled"): kapalı bölüm hiçbir sayfada gösterilmez.
 * Anahtarlar: steps, blog, features, announcements, intro, social, reviews, city_entry.
 */
class SiteSections
{
    private static ?array $disabled = null;

    public static function enabled(string $key): bool
    {
        self::$disabled ??= array_filter(explode(',', (string) Setting::many(['sections_disabled'])['sections_disabled']));

        return !in_array($key, self::$disabled, true);
    }
}
