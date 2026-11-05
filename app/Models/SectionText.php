<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

/**
 * Anasayfa bölümünün başlık yazıları (panel; key ile: "steps"…).
 */
class SectionText extends Model
{
    use HasTranslations;

    public $translatable = ['eyebrow', 'title', 'title_highlight', 'description'];

    /**
     * Geçerli dildeki yazılar; panelde o dil yoksa İngilizce, o da yoksa $fallback (lang dosyası).
     *
     * @param  array<string, string>  $fallback
     * @return array<string, string>
     */
    public static function texts(string $key, string $locale, array $fallback): array
    {
        $record = static::query()->where('key', $key)->first();

        return collect($fallback)->mapWithKeys(fn ($default, $field) => [
            $field => ($record?->getTranslation($field, $locale, false) ?: $record?->getTranslation($field, 'en', false)) ?: $default,
        ])->all();
    }
}
