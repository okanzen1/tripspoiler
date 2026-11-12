<?php

namespace App\Support;

use App\Models\City;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Hava durumu ve iklim (ücretsiz kaynaklar, anahtar gerekmez):
 *  - Tahmin: MET Norway Locationforecast (CC BY 4.0, ticari kullanım serbest; kaynak footer'da). Saatte bir yenilenir.
 *  - Gün doğumu/batımı: PHP'nin kendi hesabı (dış istek yok).
 * Servis cevap vermezse son başarılı veri kullanılır; hiç yoksa bölüm gizlenir, sayfa bozulmaz.
 */
class Weather
{
    private const FORECAST_URL = 'https://api.met.no/weatherapi/locationforecast/2.0/compact';

    /** MET Norway kimlik ister: site adı ve adresi (kişisel e-posta değil) */
    private const USER_AGENT = 'TripSpoiler/1.0 (+https://tripspoiler.com)';

    private const FORECAST_TTL = 3600;

    /** MET sembolü → bizim durumumuz (ikon + çeviri anahtarı) */
    public static function condition(?string $symbol): string
    {
        $s = preg_replace('/_(day|night|polartwilight)$/', '', (string) $symbol);

        return match (true) {
            str_contains($s, 'thunder') => 'thunder',
            str_contains($s, 'snow') => 'snow',
            str_contains($s, 'sleet') => 'sleet',
            str_starts_with($s, 'heavyrain') => 'heavy_rain',
            str_starts_with($s, 'lightrain') => 'light_rain',
            str_starts_with($s, 'rain') => 'rain',
            $s === 'fog' => 'fog',
            $s === 'cloudy' => 'cloudy',
            $s === 'partlycloudy' => 'partly',
            $s === 'fair' => 'fair',
            default => 'clear',
        };
    }

    /** Yağışlı sayılan durumlar (öneriler ve "ne giyilir" için) */
    public const WET = ['light_rain', 'rain', 'heavy_rain', 'thunder', 'sleet', 'snow'];

    /** Şehrin saat dilimi: ülkenin saat dilimlerinden şehre en yakını */
    public static function timezone(City $city): string
    {
        return Cache::rememberForever("city_tz:{$city->id}", function () use ($city) {
            $code = $city->country?->code;
            $zones = $code ? \DateTimeZone::listIdentifiers(\DateTimeZone::PER_COUNTRY, $code) : [];
            if (count($zones) <= 1 || $city->latitude === null) {
                return $zones[0] ?? 'UTC';
            }

            return collect($zones)->sortBy(function ($zone) use ($city) {
                $loc = (new \DateTimeZone($zone))->getLocation();

                return ($loc['latitude'] - $city->latitude) ** 2 + ($loc['longitude'] - $city->longitude) ** 2;
            })->first();
        });
    }

    /**
     * Bugün ve önümüzdeki 7 gün.
     *
     * @return array{now: array{temp: int, condition: string, wind: int, humidity: int}, days: list<array{date: string, min: int, max: int, rain: float, condition: string}>, tz: string, updated: int}|null
     */
    public static function forecast(City $city): ?array
    {
        if ($city->latitude === null || $city->longitude === null) {
            return null;
        }
        $key = "weather2:{$city->id}";
        $cached = Cache::get($key);
        if ($cached && $cached['fetched'] > time() - self::FORECAST_TTL) {
            return $cached;
        }

        $lock = Cache::lock("{$key}:refresh", 20);
        if (!$lock->get()) {
            return $cached ?: null;
        }
        try {
            $json = Http::timeout(5)->withUserAgent(self::USER_AGENT)->acceptJson()->get(self::FORECAST_URL, [
                'lat' => round((float) $city->latitude, 4),
                'lon' => round((float) $city->longitude, 4),
            ])->json();
            $parsed = self::parse($json, self::timezone($city));
            if ($parsed) {
                $cached = [...$parsed, 'fetched' => time()];
                Cache::forever($key, $cached);
            } elseif ($cached) {
                Cache::forever($key, [...$cached, 'fetched' => time() - self::FORECAST_TTL + 900]); // 15 dk sonra tekrar dene
            }
        } catch (\Throwable) {
            if ($cached) {
                Cache::forever($key, [...$cached, 'fetched' => time() - self::FORECAST_TTL + 900]);
            }
        } finally {
            $lock->release();
        }

        // 12 saatten eski tahmin gösterilmez
        return $cached && $cached['updated'] > time() - 12 * 3600 ? $cached : null;
    }

    private static function parse(?array $json, string $tz): ?array
    {
        $series = $json['properties']['timeseries'] ?? null;
        if (!$series) {
            return null;
        }
        $first = $series[0]['data'];
        $now = [
            'temp' => (int) round($first['instant']['details']['air_temperature'] ?? 0),
            'condition' => self::condition($first['next_1_hours']['summary']['symbol_code'] ?? $first['next_6_hours']['summary']['symbol_code'] ?? null),
            'wind' => (int) round(($first['instant']['details']['wind_speed'] ?? 0) * 3.6),
            'humidity' => (int) round($first['instant']['details']['relative_humidity'] ?? 0),
        ];

        $days = [];
        $hours = [];
        foreach ($series as $point) {
            $time = Carbon::parse($point['time'])->setTimezone($tz);
            $date = $time->toDateString();
            $temp = $point['data']['instant']['details']['air_temperature'] ?? null;
            if ($temp === null) {
                continue;
            }
            $wind = $point['data']['instant']['details']['wind_speed'] ?? 0;
            $day = $days[$date] ?? ['date' => $date, 'min' => $temp, 'max' => $temp, 'rain' => 0.0, 'wind' => 0, 'symbols' => []];
            $day['min'] = min($day['min'], $temp);
            $day['max'] = max($day['max'], $temp);
            $day['wind'] = max($day['wind'], $wind);
            // Saatlik: ilk 36 saat (hava durumu sayfasındaki saatlik şerit)
            if (count($hours) < 36 && isset($point['data']['next_1_hours'])) {
                $hours[] = [
                    'time' => $time->format('Y-m-d H:i'),
                    'temp' => (int) round($temp),
                    'condition' => self::condition($point['data']['next_1_hours']['summary']['symbol_code'] ?? null),
                    'rain' => round($point['data']['next_1_hours']['details']['precipitation_amount'] ?? 0, 1),
                    'wind' => (int) round($wind * 3.6),
                ];
            }
            // Yağış: ilk ~2,5 gün saatlik noktalar (next_1_hours), sonrası 6 saatlik noktalar (next_6_hours)
            if (isset($point['data']['next_1_hours'])) {
                $day['rain'] += $point['data']['next_1_hours']['details']['precipitation_amount'] ?? 0;
            } elseif (isset($point['data']['next_6_hours'])) {
                $day['rain'] += $point['data']['next_6_hours']['details']['precipitation_amount'] ?? 0;
            }
            // Günün havası: gündüz (09–18) sembolleri
            $symbol = $point['data']['next_6_hours']['summary']['symbol_code'] ?? $point['data']['next_1_hours']['summary']['symbol_code'] ?? null;
            if ($symbol && $time->hour >= 9 && $time->hour <= 18) {
                $day['symbols'][] = self::condition($symbol);
            }
            $days[$date] = $day;
        }

        $days = collect($days)->take(8)->map(function ($day) {
            $symbols = collect($day['symbols']);
            // En sık görülen durum; gündüzün en az üçte biri yağışlıysa ya da 2 mm'den fazla yağış varsa en sık yağış türü
            $wet = $symbols->filter(fn ($c) => in_array($c, self::WET, true));
            $isWet = $wet->isNotEmpty() && ($wet->count() >= max(2, $symbols->count() / 3) || $day['rain'] >= 2);
            $condition = $isWet
                ? $wet->countBy()->sortDesc()->keys()->first()
                : ($symbols->reject(fn ($c) => in_array($c, self::WET, true))->countBy()->sortDesc()->keys()->first() ?? 'partly');
            // Günlük toplam yağış çoksa yağmurun şiddetini ona göre yaz
            if (in_array($condition, ['light_rain', 'rain'], true)) {
                $condition = $day['rain'] >= 25 ? 'heavy_rain' : ($day['rain'] >= 8 ? 'rain' : $condition);
            }

            return [
                'date' => $day['date'],
                'min' => (int) round($day['min']),
                'max' => (int) round($day['max']),
                'rain' => round($day['rain'], 1),
                'wind' => (int) round($day['wind'] * 3.6),
                'condition' => $condition,
            ];
        })->values()->all();

        return [
            'now' => $now,
            'days' => $days,
            'hours' => $hours,
            'tz' => $tz,
            'updated' => isset($json['properties']['meta']['updated_at']) ? Carbon::parse($json['properties']['meta']['updated_at'])->getTimestamp() : time(),
        ];
    }

    /**
     * Birden çok şehrin tahmini (Hava durumu sayfası): önbellekte olmayanlar aynı anda istenir.
     *
     * @param  iterable<City>  $cities
     * @return array<int, array|null>  şehir id => forecast()
     */
    public static function many(iterable $cities): array
    {
        $cities = collect($cities)->filter(fn (City $c) => $c->latitude !== null && $c->longitude !== null);
        $stale = $cities->filter(function (City $c) {
            $cached = Cache::get("weather2:{$c->id}");

            return !$cached || $cached['fetched'] <= time() - self::FORECAST_TTL;
        });

        if ($stale->count() > 1) {
            try {
                $responses = Http::pool(fn ($pool) => $stale->map(fn (City $c) => $pool->as((string) $c->id)->timeout(6)
                    ->withUserAgent(self::USER_AGENT)->acceptJson()
                    ->get(self::FORECAST_URL, ['lat' => round((float) $c->latitude, 4), 'lon' => round((float) $c->longitude, 4)]))->all());
                foreach ($stale as $city) {
                    $response = $responses[(string) $city->id] ?? null;
                    if ($response instanceof \Illuminate\Http\Client\Response && $response->successful()
                        && ($parsed = self::parse($response->json(), self::timezone($city)))) {
                        Cache::forever("weather2:{$city->id}", [...$parsed, 'fetched' => time()]);
                    }
                }
            } catch (\Throwable) {
                // tek tek denenir (aşağıda forecast())
            }
        }

        return $cities->mapWithKeys(fn (City $c) => [$c->id => self::forecast($c)])->all();
    }

    /**
     * Gün doğumu ve batımı (şehrin saatiyle "07:12").
     *
     * @return array{sunrise: ?string, sunset: ?string, golden: ?string}
     */
    public static function sun(City $city, ?string $date = null): array
    {
        $tz = self::timezone($city);
        $today = ($date ? Carbon::parse($date, $tz) : Carbon::now($tz))->setTime(12, 0);
        $info = date_sun_info($today->getTimestamp(), (float) $city->latitude, (float) $city->longitude);
        $fmt = fn ($ts) => is_int($ts) ? Carbon::createFromTimestamp($ts, $tz)->format('H:i') : null;

        return [
            'sunrise' => $fmt($info['sunrise'] ?? null),
            'sunset' => $fmt($info['sunset'] ?? null),
            // "Altın saat": gün batımından önceki yaklaşık 1 saat
            'golden' => is_int($info['sunset'] ?? null) ? Carbon::createFromTimestamp($info['sunset'] - 3600, $tz)->format('H:i') : null,
        ];
    }

    /**
     * Önümüzdeki 3 güne göre kısa "ne giyilir" ipuçları (çeviri anahtarları).
     *
     * @param  list<array{min: int, max: int, condition: string}>  $days
     * @return list<string>
     */
    public static function tips(array $days): array
    {
        $next = array_slice($days, 0, 3);
        if (!$next) {
            return [];
        }
        $max = max(array_column($next, 'max'));
        $min = min(array_column($next, 'min'));
        $conditions = array_column($next, 'condition');
        $tips = [];
        if (array_intersect($conditions, ['snow', 'sleet'])) {
            $tips[] = 'tip_snow';
        } elseif (array_intersect($conditions, self::WET)) {
            $tips[] = 'tip_umbrella';
        }
        if ($max >= 28) {
            $tips[] = 'tip_hot';
        } elseif ($min <= 5) {
            $tips[] = 'tip_cold';
        } elseif ($min <= 14) {
            $tips[] = 'tip_jacket';
        } else {
            $tips[] = 'tip_mild';
        }

        return $tips;
    }

    /** Önümüzdeki 3 gün çoğunlukla yağışlı mı ("bu havada ne yapılır" için) */
    public static function isWet(array $days): bool
    {
        $next = array_slice($days, 0, 3);

        return count(array_filter($next, fn ($d) => in_array($d['condition'], self::WET, true))) >= 2;
    }
}
