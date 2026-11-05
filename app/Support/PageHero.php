<?php

namespace App\Support;

use App\Models\SectionText;
use App\Models\Setting;

/**
 * Sayfa hero'su (panel: Sayfalar › Aktiviteler / Şehirler / Blog › Hero ayarları).
 * Yazılar section_texts "hero_{sayfa}" (boşsa dil dosyası); :city seçili şehrin adı olur.
 * Görünüm: Setting "banner_{sayfa}_focus" (0–100 yatay kadraj) ve "banner_{sayfa}_overlay" (soft | medium | strong).
 */
class PageHero
{
    /** @param  array<string, string>  $fallback  alan => varsayılan metin */
    public static function texts(string $page, array $fallback, ?string $city = null): array
    {
        return array_map(
            fn ($text) => str_replace(':city', (string) $city, (string) $text),
            SectionText::texts("hero_{$page}", app()->getLocale(), $fallback),
        );
    }

    /** @return array{focus: int, overlay: string} */
    public static function visual(string $page): array
    {
        $settings = Setting::many(["banner_{$page}_focus", "banner_{$page}_overlay"]);
        $overlay = $settings["banner_{$page}_overlay"];

        return [
            'focus' => (int) ($settings["banner_{$page}_focus"] ?? 50),
            'overlay' => in_array($overlay, ['soft', 'medium', 'strong'], true) ? $overlay : 'medium',
        ];
    }
}
