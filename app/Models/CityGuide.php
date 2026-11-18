<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

/**
 * Şehir sayfasının üstündeki genel başlık ve açıklama + "Kısa bilgiler" tablosu (panel: Şehir rehberi › şehir).
 */
class CityGuide extends Model
{
    use HasTranslations;

    /** Kısa bilgiler: anahtar → simge (sıra sitedeki sıra; etiketler lang/*\/cities-page.php "fact_{anahtar}") */
    public const FACTS = [
        'best_time' => 'calendar',
        'days' => 'clock',
        'budget' => 'wallet',
        'airport' => 'plane',
        'transport' => 'bus',
        'currency' => 'bag',
        'language' => 'message',
        'tipping' => 'thumbs-up',
        'power' => 'zap',
        'emergency' => 'shield',
    ];

    public $translatable = ['title', 'description',
        'fact_best_time', 'fact_days', 'fact_budget', 'fact_airport', 'fact_transport', 'fact_currency', 'fact_language', 'fact_tipping', 'fact_power', 'fact_emergency'];

    protected function casts(): array
    {
        return ['facts_checked_at' => 'date'];
    }

    /**
     * Bu dilde dolu bilgiler (boşlar atlanır).
     *
     * @return list<array{key: string, icon: string, label: string, value: string}>
     */
    public function facts(): array
    {
        $facts = [];
        foreach (self::FACTS as $key => $icon) {
            $value = trim((string) $this->getTranslation('fact_' . $key, app()->getLocale()));
            if ($value !== '') {
                $facts[] = ['key' => $key, 'icon' => $icon, 'label' => __('cities-page.fact_' . $key), 'value' => $value];
            }
        }

        return $facts;
    }
}
