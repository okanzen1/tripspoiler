<?php

namespace App\Support;

/**
 * Başlıklara içinde bulunulan yıl: "Acropolis Tickets with Audio Tour | TripSpoiler" → "… 2026 | TripSpoiler".
 * Zaten bir yıl varsa ya da başlık 65 karakteri geçecekse dokunulmaz. Yıl her ocakta kendiliğinden değişir.
 */
class SeoYear
{
    public static function add(string $title): string
    {
        if (preg_match('/\b20\d{2}\b/', $title)) {
            return $title;
        }
        $year = (string) now()->year;
        $parts = preg_split('/\s+\|\s+/u', $title, 2);
        $candidate = count($parts) === 2 ? $parts[0] . ' ' . $year . ' | ' . $parts[1] : $title . ' ' . $year;

        return mb_strlen($candidate) <= 65 ? $candidate : $title;
    }
}
