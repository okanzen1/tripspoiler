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

    public $translatable = ['eyebrow', 'title_1', 'title_2', 'title_3', 'title_highlight', 'subtitle', 'description'];

    /** Alan → panelde yoksa kullanılacak eski çeviri anahtarı (lang/{dil}/home-hero.php) */
    private const FALLBACK = [
        'eyebrow' => 'eyebrow',
        'title_1' => 'title_1',
        'title_2' => 'title_2',
        'title_3' => 'title_3',
        'title_highlight' => 'title_highlight',
        'subtitle' => 'subtitle',
        'description' => 'desc',
    ];

    /**
     * Geçerli dildeki yazılar: panel (o dil, yoksa İngilizce). Panelde boş bırakılan alan boş kalır
     * (ör. 2 satırlık başlık); lang dosyası yalnızca panel hiç doldurulmamışsa (başlık yoksa) kullanılır.
     *
     * @return array<string, string>
     */
    public static function forLocale(string $locale): array
    {
        $record = static::query()->first();
        $fromPanel = filled($record?->getTranslation('title_1', 'en', false));

        return collect(self::FALLBACK)->mapWithKeys(fn ($key, $field) => [
            // İngilizcesi boşaltılan alan (ör. kaldırılan 3. satır) eski çevirisi kalmış olsa da hiçbir dilde görünmez
            $field => $fromPanel
                ? (filled($record->getTranslation($field, 'en', false)) ? (string) ($record->getTranslation($field, $locale, false) ?: $record->getTranslation($field, 'en', false)) : '')
                : (\Illuminate\Support\Facades\Lang::has("home-hero.$key") ? __("home-hero.$key") : ''),
        ])->all();
    }

    /**
     * Başlık satırları: dolu olanlar sırayla; kırmızı kelime en son satırın sonuna eklenir.
     *
     * @return list<string>
     */
    public static function titleLines(array $texts): array
    {
        $lines = array_values(array_filter([$texts['title_1'] ?? '', $texts['title_2'] ?? '', $texts['title_3'] ?? ''], 'filled'));

        return $lines ?: [''];
    }
}
