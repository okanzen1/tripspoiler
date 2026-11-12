<?php

namespace App\Support;

/**
 * Menü ikonlarının renkleri (masaüstü yan menü + telefon "sayfalar" menüsü aynı renkleri kullanır).
 * Normalde beyaz yuvarlak içinde renkli ikon; seçili sayfada yuvarlak o renkle dolar, ikon beyaz olur.
 */
class NavColors
{
    private const COLORS = [
        'home' => ['text-[#C62E2E]', 'bg-[#C62E2E] text-white shadow-[0_10px_24px_-10px_rgba(198,46,46,0.9)]'],
        'cities.*' => ['text-indigo-600', 'bg-indigo-600 text-white shadow-[0_10px_24px_-10px_rgba(79,70,229,0.9)]'],
        'blog.*' => ['text-violet-600', 'bg-violet-600 text-white shadow-[0_10px_24px_-10px_rgba(124,58,237,0.9)]'],
        'weather' => ['text-amber-500', 'bg-amber-500 text-white shadow-[0_10px_24px_-10px_rgba(245,158,11,0.9)]'],
        'time' => ['text-sky-600', 'bg-sky-600 text-white shadow-[0_10px_24px_-10px_rgba(2,132,199,0.9)]'],
        'rates' => ['text-emerald-600', 'bg-emerald-600 text-white shadow-[0_10px_24px_-10px_rgba(5,150,105,0.9)]'],
    ];

    /** Seçili değilken ikon rengi (beyaz zemin üstünde) */
    public static function icon(string $match): string
    {
        return self::COLORS[$match][0] ?? 'text-slate-700';
    }

    /** Seçili sayfa: dolu yuvarlak */
    public static function active(string $match): string
    {
        return self::COLORS[$match][1] ?? 'bg-[#C62E2E] text-white';
    }
}
