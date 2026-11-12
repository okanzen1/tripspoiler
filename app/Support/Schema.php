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

    /**
     * Sayfanın kendisi (WebPage ve alt türleri: AboutPage, ContactPage, CollectionPage…), siteye ve şirkete bağlı.
     *
     * @param  array<string, mixed>  $extra  ek alanlar (about, mainEntity, primaryImageOfPage…)
     */
    public static function webPage(string $type, string $name, ?string $description = null, array $extra = []): array
    {
        $url = self::canonical();

        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => $type,
            '@id' => $url . '#webpage',
            'url' => $url,
            'name' => $name,
            'description' => $description ? \Illuminate\Support\Str::limit(trim(strip_tags($description)), 300) : null,
            'inLanguage' => app()->getLocale(),
            'isPartOf' => ['@id' => self::websiteId()],
            'publisher' => self::organizationRef(),
            ...$extra,
        ], fn ($value) => $value !== null && $value !== []);
    }

    /**
     * Sıralı liste (ItemList): [ [ad, adres, ek alanlar?], … ]
     *
     * @param  iterable<array{0: string, 1: string, 2?: array}>  $items
     */
    public static function itemList(iterable $items): array
    {
        $list = [];
        foreach ($items as $i => [$name, $url]) {
            $list[] = ['@type' => 'ListItem', 'position' => count($list) + 1, 'name' => $name, 'url' => $url];
        }

        return ['@type' => 'ItemList', 'numberOfItems' => count($list), 'itemListElement' => $list];
    }
}
