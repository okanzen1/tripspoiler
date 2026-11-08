<?php

namespace App\Support;

/**
 * schema.org yapısal verisinin ortak parçaları. Organization ve WebSite layout'ta bir kez tanımlanır
 * (layouts/app), diğer sayfalar onlara bu @id'lerle bağlanır; böylece Google tek bir varlık görür.
 */
class Schema
{
    public static function home(): string
    {
        return rtrim((string) config('app.url'), '/') . '/';
    }

    public static function organizationId(): string
    {
        return self::home() . '#organization';
    }

    public static function websiteId(): string
    {
        return self::home() . '#website';
    }

    public static function logoUrl(): string
    {
        return asset('android-chrome-512x512.png');
    }

    /** Organization'a bağlantı (author / publisher / provider için) */
    public static function organizationRef(): array
    {
        return ['@id' => self::organizationId()];
    }

    /** Sayfanın kanonik adresi: dil ön ekli, izleme parametreleri (utm_*, gclid…) olmadan */
    public static function canonical(?string $locale = null): string
    {
        $url = \Mcamara\LaravelLocalization\Facades\LaravelLocalization::getLocalizedURL($locale ?? app()->getLocale(), null, [], true);

        return self::stripTracking((string) $url);
    }

    public static function stripTracking(string $url): string
    {
        $parts = parse_url($url);
        if (empty($parts['query'])) {
            return $url;
        }

        parse_str($parts['query'], $query);
        $query = array_filter(
            $query,
            fn ($key) => !preg_match('/^(utm_\w+|gclid|fbclid|msclkid|yclid|ttclid|mc_\w+|ref|rating)$/i', (string) $key),
            ARRAY_FILTER_USE_KEY
        );
        // ?page=1 ile sayfanın kendisi aynı
        if (($query['page'] ?? null) === '1') {
            unset($query['page']);
        }

        $base = strtok($url, '?');

        return $query ? $base . '?' . http_build_query($query) : $base;
    }
}
