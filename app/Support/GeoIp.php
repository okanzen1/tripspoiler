<?php

namespace App\Support;

use MaxMind\Db\Reader;

/**
 * IP → ülke ve şehir. Ücretsiz DB-IP Lite veritabanı sunucuda durur (dışarıya istek yok, ücretsiz):
 * storage/app/geoip/dbip-city-lite.mmdb — "php artisan geoip:update" indirir, her ay kendiliğinden yenilenir.
 * Lisans: CC BY 4.0, "IP Geolocation by DB-IP" (https://db-ip.com) atfı gerekir.
 */
class GeoIp
{
    public const PATH = 'app/geoip/dbip-city-lite.mmdb';

    private static ?Reader $reader = null;

    private static bool $missing = false;

    /** @return array{country: ?string, city: ?string} */
    public static function lookup(?string $ip): array
    {
        $empty = ['country' => null, 'city' => null];
        if (!$ip || !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) || !($reader = self::reader())) {
            return $empty;
        }

        try {
            $record = $reader->get($ip);
        } catch (\Throwable) {
            return $empty;
        }

        $country = strtoupper((string) ($record['country']['iso_code'] ?? ''));

        return [
            'country' => preg_match('/^[A-Z]{2}$/', $country) ? $country : null,
            'city' => isset($record['city']['names']['en']) ? mb_substr($record['city']['names']['en'], 0, 80) : null,
        ];
    }

    private static function reader(): ?Reader
    {
        if (self::$reader || self::$missing) {
            return self::$reader;
        }
        $path = storage_path(self::PATH);
        if (!is_file($path)) {
            self::$missing = true;

            return null;
        }

        return self::$reader = new Reader($path);
    }
}
