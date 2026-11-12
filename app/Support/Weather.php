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
    private const FORECAST_URL = 'https://api.met.no/weatherapi/locationforecast/2.0/complete';

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
        $key = "weather3:{$city->id}";
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
            // ayrıntılı tahminden (complete): hissedilen, UV, rüzgâr yönü, basınç, çiy noktası, bulut
            'feels' => isset($first['instant']['details']['apparent_air_temperature']) ? (int) round($first['instant']['details']['apparent_air_temperature']) : null,
            'uv' => $first['instant']['details']['ultraviolet_index_clear_sky'] ?? null,
            'dir' => $first['instant']['details']['wind_from_direction'] ?? null,
            'pressure' => isset($first['instant']['details']['air_pressure_at_sea_level']) ? (int) round($first['instant']['details']['air_pressure_at_sea_level']) : null,
            'dew' => isset($first['instant']['details']['dew_point_temperature']) ? (int) round($first['instant']['details']['dew_point_temperature']) : null,
            'cloud' => isset($first['instant']['details']['cloud_area_fraction']) ? (int) round($first['instant']['details']['cloud_area_fraction']) : null,
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
            $day['uv'] = max($day['uv'] ?? 0, $point['data']['instant']['details']['ultraviolet_index_clear_sky'] ?? 0);
            // Saatlik: ilk 36 saat (hava durumu sayfasındaki saatlik şerit)
            if (count($hours) < 36 && isset($point['data']['next_1_hours'])) {
                $hours[] = [
                    'time' => $time->format('Y-m-d H:i'),
                    'temp' => (int) round($temp),
                    'condition' => self::condition($point['data']['next_1_hours']['summary']['symbol_code'] ?? null),
                    'rain' => round($point['data']['next_1_hours']['details']['precipitation_amount'] ?? 0, 1),
                    'wind' => (int) round($wind * 3.6),
                    'feels' => isset($point['data']['instant']['details']['apparent_air_temperature']) ? (int) round($point['data']['instant']['details']['apparent_air_temperature']) : null,
                    'uv' => $point['data']['instant']['details']['ultraviolet_index_clear_sky'] ?? null,
                    'dir' => $point['data']['instant']['details']['wind_from_direction'] ?? null,
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
                'uv' => round($day['uv'] ?? 0, 1),
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
            $cached = Cache::get("weather3:{$c->id}");

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
                        Cache::forever("weather3:{$city->id}", [...$parsed, 'fetched' => time()]);
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
     * Önümüzdeki saatler gün parçalarına bölünmüş: sabah (06–11), öğleden sonra (12–16), akşam (17–21), gece (22–05).
     *
     * @return list<array{part: string, tomorrow: bool, temp: int, condition: string, rain: float, wind: int, from: string, to: string}>
     */
    public static function dayParts(array $forecast, int $count = 4): array
    {
        $now = Carbon::now($forecast['tz']);
        $from = $now->copy()->startOfHour()->format('Y-m-d H:i');
        $groups = [];
        foreach ($forecast['hours'] ?? [] as $hour) {
            if ($hour['time'] < $from) {
                continue;
            }
            $h = (int) substr($hour['time'], 11, 2);
            $date = substr($hour['time'], 0, 10);
            [$part, $partDate] = match (true) {
                $h >= 6 && $h < 12 => ['morning', $date],
                $h >= 12 && $h < 17 => ['afternoon', $date],
                $h >= 17 && $h < 22 => ['evening', $date],
                $h >= 22 => ['night', $date],
                default => ['night', Carbon::parse($date)->subDay()->toDateString()], // gece yarısından sonrası önceki günün gecesi
            };
            $groups[$partDate . $part] ??= ['part' => $part, 'date' => $partDate, 'hours' => []];
            $groups[$partDate . $part]['hours'][] = $hour;
        }

        return collect($groups)->take($count)->map(function ($group) use ($now) {
            $hours = collect($group['hours']);
            $wet = $hours->first(fn ($hour) => $hour['rain'] >= 0.2);

            return [
                'part' => $group['part'],
                'tomorrow' => $group['date'] > $now->toDateString(),
                'temp' => $group['part'] === 'night' ? $hours->min('temp') : $hours->max('temp'),
                'condition' => $wet['condition'] ?? $hours->get(intdiv($hours->count(), 2))['condition'],
                'rain' => round($hours->sum('rain'), 1),
                'wind' => (int) $hours->max('wind'),
                'from' => substr($hours->first()['time'], 11, 5),
                'to' => Carbon::parse($hours->last()['time'])->addHour()->format('H:i'),
            ];
        })->values()->all();
    }

    /**
     * Güneşin günü: doğuş, batış, tepe noktası, altın saatler, mavi saatler, gün uzunluğu (saniye).
     */
    public static function sunDay(City $city, ?string $date = null): array
    {
        $tz = self::timezone($city);
        $day = ($date ? Carbon::parse($date, $tz) : Carbon::now($tz))->setTime(12, 0);
        $info = date_sun_info($day->getTimestamp(), (float) $city->latitude, (float) $city->longitude);
        $ts = fn ($key) => is_int($info[$key] ?? null) ? $info[$key] : null;
        $fmt = fn (?int $t) => $t ? Carbon::createFromTimestamp($t, $tz)->format('H:i') : null;
        [$rise, $set] = [$ts('sunrise'), $ts('sunset')];

        return [
            'rise' => $rise, 'set' => $set,
            'sunrise' => $fmt($rise), 'sunset' => $fmt($set), 'noon' => $fmt($ts('transit')),
            'golden_morning' => $rise ? [$fmt($rise), $fmt($rise + 3600)] : null,
            'golden_evening' => $set ? [$fmt($set - 3600), $fmt($set)] : null,
            'blue_morning' => $ts('civil_twilight_begin') && $rise ? [$fmt($ts('civil_twilight_begin')), $fmt($rise)] : null,
            'blue_evening' => $ts('civil_twilight_end') && $set ? [$fmt($set), $fmt($ts('civil_twilight_end'))] : null,
            'length' => $rise && $set ? $set - $rise : null,
        ];
    }

    /**
     * Ayın evresi (hesapla; servis gerekmez): evre adı, 0–1 arası döngü, aydınlık yüzdesi, sonraki dolunay.
     */
    public static function moon(?Carbon $at = null): array
    {
        $at ??= Carbon::now();
        $synodic = 29.530588853;
        $age = fmod(($at->getTimestamp() - 947182440) / 86400, $synodic); // bilinen yeni ay: 6 Ocak 2000 18:14 UTC
        if ($age < 0) {
            $age += $synodic;
        }
        $fraction = $age / $synodic;
        $phase = match (true) {
            $age < 1.84 => 'new',
            $age < 5.53 => 'waxing_crescent',
            $age < 9.22 => 'first_quarter',
            $age < 12.91 => 'waxing_gibbous',
            $age < 16.61 => 'full',
            $age < 20.30 => 'waning_gibbous',
            $age < 23.99 => 'last_quarter',
            $age < 27.68 => 'waning_crescent',
            default => 'new',
        };

        return [
            'phase' => $phase,
            'fraction' => $fraction,
            'lit' => (int) round((1 - cos(2 * M_PI * $fraction)) / 2 * 100),
            'next_full' => $at->copy()->addSeconds((int) round((($fraction < 0.5 ? 0.5 : 1.5) - $fraction) * $synodic * 86400)),
        ];
    }

    public static function uvLevel(float $uv): string
    {
        return match (true) {
            $uv < 3 => 'low',
            $uv < 6 => 'moderate',
            $uv < 8 => 'high',
            $uv < 11 => 'very_high',
            default => 'extreme',
        };
    }

    /** Rüzgârın geldiği yön (derece) → 8 yön anahtarı */
    public static function direction(float $degrees): string
    {
        return ['n', 'ne', 'e', 'se', 's', 'sw', 'w', 'nw'][(int) round(fmod($degrees + 360, 360) / 45) % 8];
    }

    public static function windLevel(int $kmh): string
    {
        return match (true) {
            $kmh < 6 => 'calm',
            $kmh < 20 => 'light',
            $kmh < 39 => 'moderate',
            $kmh < 62 => 'strong',
            default => 'storm',
        };
    }

    /**
     * Günün "en iyi saatleri": fotoğraf (altın saat), spor / yürüyüş, dışarıda kahve, dışarıda akşam yemeği, yağmurun başlayacağı saat.
     * Bugünkü saat geçtiyse ertesi gün ("tomorrow"). Uygun saat bulunamayan kart çıkmaz.
     *
     * @return list<array{kind: string, time: string, tomorrow: bool, temp?: int, condition?: string, sunset?: string, rain?: float}>
     */
    public static function bestTimes(City $city, array $forecast): array
    {
        $now = Carbon::now($forecast['tz']);
        $today = $now->toDateString();
        $from = $now->copy()->startOfHour()->format('Y-m-d H:i');
        $hours = $forecast['hours'] ?? [];
        $times = [];

        $sun = self::sun($city);
        $photoTomorrow = $sun['sunset'] && $now->format('H:i') > $sun['sunset'];
        if ($photoTomorrow) {
            $sun = self::sun($city, $now->copy()->addDay()->toDateString());
        }
        if ($sun['golden']) {
            $times[] = ['kind' => 'photo', 'time' => $sun['golden'], 'tomorrow' => $photoTomorrow, 'sunset' => $sun['sunset']];
        }
        // spor: sıcaklığı 17°'ye yakın; kahve: 22°'ye yakın, öğleden sonra; akşam yemeği: 18–22 arası
        foreach (['sport' => [17, 7, 20, 13], 'coffee' => [22, 10, 19, 15], 'dinner' => [21, 18, 22, 20]] as $kind => [$ideal, $start, $end, $mid]) {
            if ($hour = self::bestOutdoorHour($hours, $from, $ideal, $start, $end, $mid)) {
                $times[] = ['kind' => $kind, 'time' => substr($hour['time'], 11, 5), 'tomorrow' => substr($hour['time'], 0, 10) !== $today, 'temp' => $hour['temp'], 'condition' => $hour['condition']];
            }
        }
        // yağmur: önümüzdeki saatlerde ilk yağışlı saat (şu an yağıyorsa gösterilmez)
        $rainy = collect($hours)->filter(fn ($hour) => $hour['time'] >= $from);
        if (($rainy->first()['rain'] ?? 0) < 0.2 && $start = $rainy->first(fn ($hour) => $hour['rain'] >= 0.2)) {
            $times[] = ['kind' => 'rain', 'time' => substr($start['time'], 11, 5), 'tomorrow' => substr($start['time'], 0, 10) !== $today,
                'rain' => round($rainy->filter(fn ($hour) => substr($hour['time'], 0, 10) === substr($start['time'], 0, 10))->sum('rain'), 1)];
        }

        // en fazla 4 kart: yağmur varsa akşam yemeği çıkar
        if (count($times) > 4) {
            $times = array_values(array_filter($times, fn ($item) => $item['kind'] !== 'dinner'));
        }

        return $times;
    }

    /**
     * Dışarıda bir şey yapmak için en rahat saat: gündüz, yağışsız, rüzgârı az, sıcaklığı $ideal'e en yakın.
     * Bugün uygun saat kalmadıysa ertesi gün. Uygun saat yoksa null.
     *
     * @param  list<array{time: string, temp: int, condition: string, rain: float, wind?: int}>  $hours
     * @return array{time: string, temp: int, condition: string}|null
     */
    public static function bestOutdoorHour(array $hours, string $from, int $ideal = 17, int $start = 7, int $end = 20, int $mid = 13): ?array
    {
        $good = collect($hours)->filter(function ($hour) use ($from, $start, $end) {
            $h = (int) substr($hour['time'], 11, 2);

            return $hour['time'] >= $from && $h >= $start && $h <= $end && $hour['rain'] < 0.1
                && !in_array($hour['condition'], [...self::WET, 'snow', 'sleet'], true);
        });
        if ($good->isEmpty()) {
            return null;
        }
        $date = substr($good->first()['time'], 0, 10);

        return $good->filter(fn ($hour) => str_starts_with($hour['time'], $date))
            ->sortBy(fn ($hour) => abs($hour['temp'] - $ideal) + max(0, ($hour['wind'] ?? 0) - 15) / 5 + abs((int) substr($hour['time'], 11, 2) - $mid) / 10) // eşitlikte gün ortası
            ->first();
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
        } else {
            $tips[] = 'tip_dry';
        }
        // rüzgâr (günün en sert saati, km/s): lodos / poyraz günleri
        $wind = max(array_map(fn ($day) => $day['wind'] ?? 0, $next));
        if ($wind >= 50) {
            $tips[] = 'tip_gale';
        } elseif ($wind >= 30) {
            $tips[] = 'tip_windy';
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
