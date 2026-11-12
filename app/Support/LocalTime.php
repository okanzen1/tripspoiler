<?php

namespace App\Support;

use App\Models\City;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * Yerel saat: şehrin saat dilimi (Weather::timezone), UTC farkı, yaz saati değişimi ve dünyadaki bütün saat dilimleri
 * (saat farkı hesaplayıcı için). Dış servis yok; PHP'nin kendi saat dilimi verisi kullanılır.
 */
class LocalTime
{
    /**
     * Şehrin saat bilgileri.
     *
     * @return array{tz: string, offset: string, offset_minutes: int, abbr: string, dst_now: bool, next_change: ?array{date: string, offset: string}}
     */
    public static function facts(City $city): array
    {
        $tz = Weather::timezone($city);
        $zone = new \DateTimeZone($tz);
        $now = Carbon::now($tz);
        $transitions = $zone->getTransitions($now->getTimestamp(), $now->copy()->addYear()->getTimestamp());
        $next = $transitions[1] ?? null;

        return [
            'tz' => $tz,
            'offset' => self::formatOffset($now->getOffset()),
            'offset_minutes' => intdiv($now->getOffset(), 60),
            'abbr' => $now->format('T'),
            'dst_now' => (bool) $now->format('I'),
            'next_change' => $next ? [
                'date' => Carbon::createFromTimestamp($next['ts'], $tz)->toDateString(),
                'offset' => self::formatOffset($next['offset']),
            ] : null,
        ];
    }

    /** "UTC+3", "UTC+5:30", "UTC−4" */
    public static function formatOffset(int $seconds): string
    {
        $sign = $seconds >= 0 ? '+' : '−';
        $seconds = abs($seconds);
        $h = intdiv($seconds, 3600);
        $m = intdiv($seconds % 3600, 60);

        return 'UTC' . $sign . $h . ($m ? ':' . str_pad((string) $m, 2, '0', STR_PAD_LEFT) : '');
    }

    /**
     * Dünyadaki saat dilimleri (hesaplayıcının listesi): [id, şehir adı, ülke adı (ziyaretçinin dilinde), ülke kodu]
     *
     * @return list<array{0: string, 1: string, 2: string, 3: string}>
     */
    public static function zones(): array
    {
        $locale = app()->getLocale();

        return Cache::rememberForever("time_zones:{$locale}", function () use ($locale) {
            $zones = [];
            foreach (\DateTimeZone::listIdentifiers() as $id) {
                if (!str_contains($id, '/') || str_starts_with($id, 'Etc/')) {
                    continue;
                }
                $code = (string) ((new \DateTimeZone($id))->getLocation()['country_code'] ?? '');
                $code = $code === '??' ? '' : $code;
                $name = str_replace('_', ' ', substr($id, strrpos($id, '/') + 1));
                $country = $code ? (\Locale::getDisplayRegion('-' . $code, $locale) ?: $code) : '';
                $zones[] = [$id, $name, $country, $code];
            }
            $collator = new \Collator($locale);
            usort($zones, fn ($a, $b) => $collator->compare($a[1], $b[1]));

            return $zones;
        });
    }
}
