<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

/**
 * Anasayfa hero yazıları (panel > Site ayarları > Hero alanı > Yazılar). Tek kayıt, çok dilli.
 */
class HeroText extends Model
{
    use HasTranslations;

    public $translatable = ['eyebrow', 'title_1', 'title_2', 'title_3', 'title_highlight', 'description'];

    /** Alan → panelde yoksa kullanılacak eski çeviri anahtarı (lang/{dil}/home-hero.php) */
    private const FALLBACK = [
        'eyebrow' => 'eyebrow',
        'title_1' => 'title_1',
        'title_2' => 'title_2',
        'title_3' => 'title_3',
        'title_highlight' => 'title_highlight',
        'description' => 'desc',
    ];

    /**
     * Geçerli dildeki yazılar: önce panel (o dil, yoksa İngilizce), o da yoksa lang dosyası.
     *
     * @return array<string, string>
     */
    public static function forLocale(string $locale): array
    {
        $record = static::query()->first();

        return collect(self::FALLBACK)->mapWithKeys(fn ($key, $field) => [
            $field => ($record?->getTranslation($field, $locale, false) ?: $record?->getTranslation($field, 'en', false)) ?: __("home-hero.$key"),
        ])->all();
    }
}
