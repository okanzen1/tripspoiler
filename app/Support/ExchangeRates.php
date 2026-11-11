<?php

namespace App\Support;

use App\Models\City;
use App\Models\Country;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Döviz kurları: ücretsiz ExchangeRate-API "open access" (anahtar yok, günde bir güncellenir).
 * Kaynak linki gösterilmesi şart (rates-bar'da var). Kurlar 6 saatte bir yenilenir; servis
 * cevap vermezse son başarılı kurlar kullanılır (sayfa hiç beklemez / bozulmaz).
 */
class ExchangeRates
{
    private const URL = 'https://open.er-api.com/v6/latest/USD';

    private const CACHE_KEY = 'fx_rates_usd';

    private const FRESH_SECONDS = 6 * 3600;

    /** Konum bulunamazsa sitenin diline göre ziyaretçinin parası */
    private const LOCALE_CURRENCY = [
        'tr' => 'TRY', 'en' => 'USD', 'de' => 'EUR', 'fr' => 'EUR', 'es' => 'EUR', 'it' => 'EUR', 'pt' => 'EUR',
        'ru' => 'RUB', 'ar' => 'AED', 'ja' => 'JPY', 'ko' => 'KRW', 'zh' => 'CNY',
    ];

    /**
     * @return array{rates: array<string, float>, updated: int}|null  1 USD karşılığı kurlar
     */
    /** Bu kadar eski kurlar gösterilmez (servis kapanırsa sitede eski kur kalmasın) */
    private const MAX_AGE_SECONDS = 3 * 86400;

    public static function table(): ?array
    {
        $table = self::load();

        return $table && $table['updated'] > time() - self::MAX_AGE_SECONDS ? $table : null;
    }

    private static function load(): ?array
    {
        $cached = Cache::get(self::CACHE_KEY);
        if ($cached && $cached['fetched'] > time() - self::FRESH_SECONDS) {
            return $cached;
        }

        // Aynı anda gelen istekler servise yüklenmesin: biri yenilerken diğerleri eskisini kullanır
        $lock = Cache::lock(self::CACHE_KEY . ':refresh', 30);
        if (!$lock->get()) {
            return $cached ?: null;
        }

        try {
            $json = Http::timeout(4)->acceptJson()->get(self::URL)->json();
            if (($json['result'] ?? null) === 'success' && !empty($json['rates']['EUR'])) {
                $updated = (int) ($json['time_last_update_unix'] ?? time());
                // Gün değiştiyse eski kurlar "önceki" olur: şeritte ▲▼ değişim gösterilir
                $previous = $cached && $cached['updated'] !== $updated ? $cached['rates'] : ($cached['previous'] ?? null);
                $cached = [
                    'rates' => array_map('floatval', $json['rates']),
                    'previous' => $previous,
                    'updated' => $updated,
                    'fetched' => time(),
                ];
                Cache::forever(self::CACHE_KEY, $cached);
            } elseif ($cached) {
                // Başarısız: eski kurlarla devam, bir saat sonra tekrar dene
                Cache::forever(self::CACHE_KEY, [...$cached, 'fetched' => time() - self::FRESH_SECONDS + 3600]);
            }
        } catch (\Throwable) {
            if ($cached) {
                Cache::forever(self::CACHE_KEY, [...$cached, 'fetched' => time() - self::FRESH_SECONDS + 3600]);
            }
        } finally {
            $lock->release();
        }

        return $cached ?: null;
    }

    /**
     * Ziyaretçinin parası: bulunduğu ülkeye göre, bulunamazsa sitenin diline göre.
     */
    public static function visitorCurrency(): string
    {
        $country = GeoIp::lookup(request()->ip())['country'];

        return self::currencyOf($country) ?? self::LOCALE_CURRENCY[app()->getLocale()] ?? 'USD';
    }

    public static function currencyOf(?string $countryCode): ?string
    {
        if (!$countryCode) {
            return null;
        }
        $currency = (new \NumberFormatter('en_' . $countryCode, \NumberFormatter::CURRENCY))->getTextAttribute(\NumberFormatter::CURRENCY_CODE);

        return preg_match('/^[A-Z]{3}$/', (string) $currency) && $currency !== 'XXX' ? $currency : null;
    }

    /** Şeridin sonundaki genel kurlar (turistin tanıdığı paralar) */
    private const MAJOR_PAIRS = [['EUR', 'USD'], ['GBP', 'USD'], ['GBP', 'EUR'], ['USD', 'JPY']];

    /**
     * Şerit: turistin anlayacağı dille. Önce sitedeki her ülke (bayrak + ad + "1 € = 56,20 ₺ · 1 $ = 50,24 ₺"),
     * sonra genel kurlar ("1 € = 1,12 $"). Ziyaretçinin konumuna göre değişmez; şehir sayfasındaysa o ülke en başta.
     *
     * @return array{items: list<array{flag: ?string, country: ?string, parts: list<string>, change: ?float, current: bool}>, updated: int}|null
     */
    public static function strip(?City $city = null): ?array
    {
        $table = self::table();
        if (!$table) {
            return null;
        }
        $rates = $table['rates'];
        $previous = $table['previous'] ?? null;
        $locale = app()->getLocale();
        $cityCountryId = $city?->country_id;

        $countries = Country::where('active', true)
            ->whereHas('cities', fn ($q) => $q->where('active', true))
            ->whereNotNull('currency')
            ->get(['id', 'code', 'currency'])
            ->filter(fn (Country $c) => isset($rates[$c->currency]))
            ->sortByDesc(fn (Country $c) => $c->id === $cityCountryId);

        $items = [];
        $shown = [];
        foreach ($countries as $country) {
            $currency = $country->currency;
            $bases = array_slice(array_values(array_diff(['EUR', 'USD', 'GBP'], [$currency])), 0, 2);
            $items[] = [
                'flag' => self::flag($country->code),
                'country' => \Locale::getDisplayRegion('-' . $country->code, $locale) ?: $country->code,
                'parts' => array_map(fn ($base) => self::line($rates, $base, $currency), $bases),
                'change' => self::change($rates, $previous, $bases[0], $currency),
                'current' => $country->id === $cityCountryId,
            ];
            foreach ($bases as $base) {
                $shown[] = "{$base}/{$currency}";
            }
        }

        foreach (self::MAJOR_PAIRS as [$base, $quote]) {
            if (in_array("{$base}/{$quote}", $shown, true) || !isset($rates[$base], $rates[$quote])) {
                continue;
            }
            $items[] = [
                'flag' => null,
                'country' => null,
                'parts' => [self::line($rates, $base, $quote)],
                'change' => self::change($rates, $previous, $base, $quote),
                'current' => false,
            ];
        }

        return ['items' => $items, 'updated' => $table['updated']];
    }

    /** "1 € = 56,20 ₺" */
    private static function line(array $rates, string $base, string $quote): string
    {
        $value = $rates[$quote] / $rates[$base];

        return self::money(1, $base, 0) . ' = ' . self::money($value, $quote, $value >= 1000 ? 0 : 2);
    }

    private static function change(array $rates, ?array $previous, string $base, string $quote): ?float
    {
        if (!isset($previous[$base], $previous[$quote]) || $previous[$base] <= 0 || $previous[$quote] <= 0) {
            return null;
        }
        $now = $rates[$quote] / $rates[$base];
        $old = $previous[$quote] / $previous[$base];

        return round(($now - $old) / $old * 100, 2);
    }

    private static function flag(string $code): string
    {
        return implode('', array_map(fn ($c) => mb_chr(0x1F1E6 + ord($c) - 65), str_split(strtoupper($code))));
    }

    /** Kısa sembollü para: €1, 56,20 ₺ (dilin yazım düzeniyle); sembolü olmayan parada kod */
    private static function money(float $amount, string $currency, int $decimals): string
    {
        static $narrow = null;
        $narrow ??= \ResourceBundle::create('root', 'ICUDATA-curr')['Currencies%narrow'] ?? [];
        $formatter = new \NumberFormatter(app()->getLocale(), \NumberFormatter::CURRENCY);
        $formatter->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, $decimals);
        $formatter->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, $decimals);
        $formatter->setTextAttribute(\NumberFormatter::CURRENCY_CODE, $currency);
        $formatter->setSymbol(\NumberFormatter::CURRENCY_SYMBOL, $narrow[$currency] ?? $currency);

        return $formatter->format($amount);
    }

    /** Kur sayfasında "Popüler" grubunda gösterilen paralar */
    public const POPULAR = ['EUR', 'USD', 'GBP', 'CHF', 'JPY', 'CNY', 'CAD', 'AUD', 'AED', 'SAR', 'RUB', 'KRW', 'SEK', 'NOK', 'DKK', 'PLN', 'CZK', 'HUF', 'TRY', 'INR'];

    /**
     * Kur sayfası (/exchange-rates) için veri: tüm kurlar (1 USD karşılığı), para adları ziyaretçinin dilinde,
     * ziyaretçinin parası ve sitedeki ülkelerin paraları.
     *
     * @return array{rates: array<string, float>, updated: int, own: string, destinations: list<string>, names: array<string, array{0: string, 1: string}>}|null
     */
    public static function page(): ?array
    {
        $table = self::table();
        if (!$table) {
            return null;
        }
        $rates = $table['rates'];

        $bundle = \ResourceBundle::create(app()->getLocale(), 'ICUDATA-curr')['Currencies'] ?? null;
        $narrow = \ResourceBundle::create('root', 'ICUDATA-curr')['Currencies%narrow'] ?? [];
        $names = [];
        foreach (array_keys($rates) as $code) {
            $info = $bundle[$code] ?? null;
            if ($info) {
                $names[$code] = [(string) $info[1], (string) ($narrow[$code] ?? $info[0])];
            }
        }
        $rates = array_intersect_key($rates, $names);
        uksort($names, fn ($a, $b) => strcmp($a, $b));

        $own = self::visitorCurrency();
        try {
            $destinations = Country::where('active', true)
                ->whereHas('cities', fn ($q) => $q->where('active', true))
                ->whereNotNull('currency')
                ->distinct()
                ->pluck('currency')
                ->filter(fn ($c) => isset($rates[$c]))
                ->values()
                ->all();
        } catch (\Throwable $e) {
            report($e); // para birimi alanı henüz yoksa sayfa yine açılsın
            $destinations = [];
        }

        $previous = array_intersect_key($table['previous'] ?? [], $rates);

        return [
            'rates' => $rates,
            'previous' => $previous ?: null,
            'updated' => $table['updated'],
            'own' => isset($rates[$own]) ? $own : 'USD',
            'destinations' => $destinations,
            'names' => $names,
        ];
    }
}
