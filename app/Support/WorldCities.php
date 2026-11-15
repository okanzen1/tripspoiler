<?php

namespace App\Support;

use App\Models\City;
use Illuminate\Support\Collection;

/**
 * Hava durumu için dünya şehirleri: sitede yayında olmasalar da (aktivitesi yok) hava durumu sayfasında ve
 * döviz şeridindeki dönen kutularda görünür. Konumları cities tablosunda kayıtlı; panelden açmaya gerek yok.
 * Adlar veritabanında İngilizce; burada sitenin dillerinde.
 */
class WorldCities
{
    /** adres adı (cities.slug) => dile göre ad (yoksa İngilizce) */
    public const NAMES = [
        'london' => ['en' => 'London', 'tr' => 'Londra', 'de' => 'London', 'fr' => 'Londres', 'es' => 'Londres', 'it' => 'Londra', 'pt' => 'Londres', 'ru' => 'Лондон', 'ar' => 'لندن', 'ja' => 'ロンドン', 'ko' => '런던', 'zh' => '伦敦'],
        'paris' => ['en' => 'Paris', 'tr' => 'Paris', 'de' => 'Paris', 'fr' => 'Paris', 'es' => 'París', 'it' => 'Parigi', 'pt' => 'Paris', 'ru' => 'Париж', 'ar' => 'باريس', 'ja' => 'パリ', 'ko' => '파리', 'zh' => '巴黎'],
        'new-york-city' => ['en' => 'New York', 'tr' => 'New York', 'de' => 'New York', 'fr' => 'New York', 'es' => 'Nueva York', 'it' => 'New York', 'pt' => 'Nova Iorque', 'ru' => 'Нью-Йорк', 'ar' => 'نيويورك', 'ja' => 'ニューヨーク', 'ko' => '뉴욕', 'zh' => '纽约'],
        'tokyo' => ['en' => 'Tokyo', 'tr' => 'Tokyo', 'de' => 'Tokio', 'fr' => 'Tokyo', 'es' => 'Tokio', 'it' => 'Tokyo', 'pt' => 'Tóquio', 'ru' => 'Токио', 'ar' => 'طوكيو', 'ja' => '東京', 'ko' => '도쿄', 'zh' => '东京'],
        'dubai' => ['en' => 'Dubai', 'tr' => 'Dubai', 'de' => 'Dubai', 'fr' => 'Dubaï', 'es' => 'Dubái', 'it' => 'Dubai', 'pt' => 'Dubai', 'ru' => 'Дубай', 'ar' => 'دبي', 'ja' => 'ドバイ', 'ko' => '두바이', 'zh' => '迪拜'],
        'rome' => ['en' => 'Rome', 'tr' => 'Roma', 'de' => 'Rom', 'fr' => 'Rome', 'es' => 'Roma', 'it' => 'Roma', 'pt' => 'Roma', 'ru' => 'Рим', 'ar' => 'روما', 'ja' => 'ローマ', 'ko' => '로마', 'zh' => '罗马'],
        'barcelona' => ['en' => 'Barcelona', 'tr' => 'Barselona', 'de' => 'Barcelona', 'fr' => 'Barcelone', 'es' => 'Barcelona', 'it' => 'Barcellona', 'pt' => 'Barcelona', 'ru' => 'Барселона', 'ar' => 'برشلونة', 'ja' => 'バルセロナ', 'ko' => '바르셀로나', 'zh' => '巴塞罗那'],
        'amsterdam' => ['en' => 'Amsterdam', 'tr' => 'Amsterdam', 'de' => 'Amsterdam', 'fr' => 'Amsterdam', 'es' => 'Ámsterdam', 'it' => 'Amsterdam', 'pt' => 'Amesterdão', 'ru' => 'Амстердам', 'ar' => 'أمستردام', 'ja' => 'アムステルダム', 'ko' => '암스테르담', 'zh' => '阿姆斯特丹'],
        'berlin' => ['en' => 'Berlin', 'tr' => 'Berlin', 'de' => 'Berlin', 'fr' => 'Berlin', 'es' => 'Berlín', 'it' => 'Berlino', 'pt' => 'Berlim', 'ru' => 'Берлин', 'ar' => 'برلين', 'ja' => 'ベルリン', 'ko' => '베를린', 'zh' => '柏林'],
        'sydney' => ['en' => 'Sydney', 'tr' => 'Sidney', 'de' => 'Sydney', 'fr' => 'Sydney', 'es' => 'Sídney', 'it' => 'Sydney', 'pt' => 'Sydney', 'ru' => 'Сидней', 'ar' => 'سيدني', 'ja' => 'シドニー', 'ko' => '시드니', 'zh' => '悉尼'],
        'bangkok' => ['en' => 'Bangkok', 'tr' => 'Bangkok', 'de' => 'Bangkok', 'fr' => 'Bangkok', 'es' => 'Bangkok', 'it' => 'Bangkok', 'pt' => 'Banguecoque', 'ru' => 'Бангкок', 'ar' => 'بانكوك', 'ja' => 'バンコク', 'ko' => '방콕', 'zh' => '曼谷'],
        'istanbul' => ['en' => 'Istanbul', 'tr' => 'İstanbul', 'de' => 'Istanbul', 'fr' => 'Istanbul', 'es' => 'Estambul', 'it' => 'Istanbul', 'pt' => 'Istambul', 'ru' => 'Стамбул', 'ar' => 'إسطنبول', 'ja' => 'イスタンブール', 'ko' => '이스탄불', 'zh' => '伊斯坦布尔'],
    ];

    /** @return list<string> */
    public static function slugs(): array
    {
        return array_keys(self::NAMES);
    }

    /** Şehrin sitenin dilindeki adı (listede yoksa veritabanındaki ad) */
    public static function name(City $city, ?string $locale = null): string
    {
        $names = self::NAMES[$city->slug] ?? null;

        return $names ? ($names[$locale ?? app()->getLocale()] ?? $names['en']) : $city->name;
    }

    /** Dünya şehirleri (konumu kayıtlı olanlar), listedeki sırayla; adları sitenin dilinde (yalnızca gösterim için, kaydedilmez) */
    public static function cities(): Collection
    {
        return City::with('country')->whereIn('slug', self::slugs())->whereNotNull('latitude')->get()
            ->sortBy(fn (City $c) => array_search($c->slug, self::slugs()))->values()
            ->each(fn (City $c) => $c->setAttribute('name', self::name($c)));
    }
}
