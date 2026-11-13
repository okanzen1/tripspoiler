<?php

namespace App\Support;

/**
 * Sosyal medya hesapları (footer + anasayfa üst bölümü aynı listeyi kullanır).
 * [ad, adres, kullanıcı adı, ikon (svg path), renk sınıfları]
 */
class Socials
{
    public static function all(): array
    {
        return [
            ['Instagram', 'https://instagram.com/tripspoilerofficial', '@tripspoilerofficial',
                '<path d="M7 2C4.24 2 2 4.24 2 7v10c0 2.76 2.24 5 5 5h10c2.76 0 5-2.24 5-5V7c0-2.76-2.24-5-5-5H7zm10 2a3 3 0 013 3v10a3 3 0 01-3 3H7a3 3 0 01-3-3V7a3 3 0 013-3h10z"/><path d="M12 7.5A4.5 4.5 0 1016.5 12 4.51 4.51 0 0012 7.5zm0 7.2A2.7 2.7 0 1114.7 12 2.7 2.7 0 0112 14.7z"/><circle cx="17.5" cy="6.5" r="1"/>',
                'bg-[radial-gradient(circle_at_30%_110%,#fdf497_0%,#fd5949_45%,#d6249f_60%,#285AEB_90%)]'],
            ['TikTok', 'https://tiktok.com/@tripspoilerofficial', '@tripspoilerofficial',
                '<path d="M21 8.3a6.6 6.6 0 01-3.8-1.2v7.1a6.4 6.4 0 11-6.4-6.4c.4 0 .8 0 1.2.1v3.3a3.1 3.1 0 102.2 3v-12h3.2a6.6 6.6 0 003.6 3.1z"/>',
                'bg-black shadow-[-2px_-2px_0_#25F4EE,2px_2px_0_#FE2C55]'],
        ];
    }
}
