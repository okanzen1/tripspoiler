<?php

namespace App\Support;

/**
 * Dil düğmelerinin arka planı: o dilin ülke bayrağının renklerinden geçiş (beyaz ağırlıklı bayraklarda beyaz atlanır,
 * üstündeki beyaz yazı okunsun diye).
 */
class LocaleColors
{
    private const COLORS = [
        'en' => ['#012169', '#C8102E'],
        'de' => ['#000000', '#DD0000', '#FFCE00'],
        'fr' => ['#0055A4', '#EF4135'],
        'es' => ['#AA151B', '#F1BF00'],
        'it' => ['#009246', '#CE2B37'],
        'pt' => ['#006600', '#FF0000'],
        'tr' => ['#E30A17', '#B5000C'],
        'ar' => ['#006C35', '#00512A'],
        'ru' => ['#0039A6', '#D52B1E'],
        'ja' => ['#BC002D', '#8E0022'],
        'zh' => ['#DE2910', '#FFDE00'],
        'ko' => ['#003478', '#C60C30'],
    ];

    public static function gradient(string $locale): string
    {
        $colors = self::COLORS[$locale] ?? ['#0f172a', '#334155'];

        return 'background-image: linear-gradient(135deg, ' . implode(', ', $colors) . ')';
    }
}
