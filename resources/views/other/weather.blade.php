@extends('layouts.app')

@section('title', $city ? __('weather.page_city_title', ['city' => $city->name]) : __('weather.page_meta_title'))
@section('meta_description', __('weather.page_meta_description'))

{{--
    Hava durumu (/weather?city=…): seçili şehrin detaylı tahmini ve bütün şehirlerin listesi.
    Üst: şehrin fotoğrafı (Sayfalar › Hava durumu › Banner, yoksa şehrin banner'ı) üzerinde başlık + şehir seçimi, sağda anlık hava.
    Sonra: saatlik şerit, bugün (gün parçaları, ne giysem, en iyi saatler), ayrıntılar (hissedilen, UV, rüzgâr yönü, nem, basınç, bulut), 7 gün + bu havada ne yapılır, güneş ve ay, şehir listesi (arama, ülke filtresi; tek ülkede filtre yok).
    Şehir sayfasındaki hava bölümünün "Detaylı tahmin" bağlantısı buraya gelir.
--}}
@php
    $locale = app()->getLocale();
    $hero = \App\Support\PageHero::texts('weather', [
        'eyebrow' => __('weather.page_eyebrow'), 'title' => __('weather.page_title'), 'description' => __('weather.page_desc'),
    ]);
    $look = \App\Support\PageHero::visual('weather', $banner['focus'] ?? null);
    // Karartma panelden (Sayfalar › sayfa › Hero alanı › Görünüm): Az = fotoğraf canlı, sadece yazı tarafı hafif koyu
    $overlayClass = ['soft' => 'from-slate-950/75 via-slate-950/30 to-transparent', 'medium' => 'from-slate-950/90 via-slate-950/60 to-slate-950/20', 'strong' => 'from-slate-950 via-slate-950/85 to-slate-950/45'][$look['overlay']];
    $bottomClass = ['soft' => 'from-slate-950/40', 'medium' => 'from-slate-950/65', 'strong' => 'from-slate-950/80'][$look['overlay']];
    $flag = fn ($code) => $code ? implode('', array_map(fn ($c) => mb_chr(0x1F1E6 + ord($c) - 65), str_split(strtoupper($code)))) : '';
    $countryName = fn ($country) => $country ? (\Locale::getDisplayRegion('-' . $country->code, $locale) ?: $country->name) : '';
    $mm = fn ($v) => rtrim(rtrim(number_format($v, 1, ',', ''), '0'), ',');

    if ($forecast) {
        $tz = $forecast['tz'];
        $now = \Illuminate\Support\Carbon::now($tz);
        $todayDate = $now->toDateString();
        $days = collect($forecast['days'])->filter(fn ($d) => $d['date'] >= $todayDate)->take(7)->values();
        $today = $days->first();
        $hours = collect($forecast['hours'] ?? [])->filter(fn ($h) => $h['time'] >= $now->copy()->startOfHour()->format('Y-m-d H:i'))->take(24)->values();
        $sun = \App\Support\Weather::sun($city);
        $tips = \App\Support\Weather::tips([$today]); // "Bugün ne giysem": sadece bugünün havasına göre
        $wet = \App\Support\Weather::isWet($forecast['days']);
        $parts = \App\Support\Weather::dayParts($forecast);
        $sunDay = \App\Support\Weather::sunDay($city);
        $moon = \App\Support\Weather::moon();
        $current = $forecast['now'];
        $duration = fn (int $seconds) => __('weather.duration', ['h' => intdiv($seconds, 3600), 'm' => intdiv($seconds % 3600, 60)]);
    }
@endphp

@section('content')
    @if (!$city || !$forecast)
        <section class="bg-slate-950 py-28 text-center text-white">
            <h1 class="text-4xl font-extrabold tracking-tight">{{ $hero['title'] }}</h1>
            <p class="mx-auto mt-4 max-w-xl text-white/60">{{ __('weather.unavailable') }}</p>
        </section>
    @else
        {{-- ÜST: şehrin fotoğrafı + anlık hava --}}
        <section id="top" x-data="{ picker: false }" :class="picker ? 'z-40' : 'z-20'" class="relative isolate z-20 scroll-mt-24 overflow-x-clip bg-slate-950 text-white">
            <picture class="absolute inset-0 -z-20 overflow-hidden">
                @if ($banner['mobile'] ?? null)<source media="(max-width: 767px)" srcset="{{ $banner['mobile'] }}">@endif
                <img src="{{ $banner['desktop'] }}" alt="" fetchpriority="high" class="h-full w-full object-cover" style="object-position: {{ $look['focus'] }}% center">
            </picture>
            <div class="absolute inset-0 -z-10 bg-gradient-to-r {{ $overlayClass }} rtl:bg-gradient-to-l"></div>
            <div class="absolute inset-x-0 bottom-0 -z-10 h-1/3 bg-gradient-to-t {{ $bottomClass }} to-transparent"></div>

            <div class="mx-auto max-w-7xl px-4 pb-14 pt-6 md:pb-20 md:pt-8">
                <x-breadcrumbs dark :items="[
                    ['label' => __('activity-show.home'), 'url' => route('home')],
                    ['label' => __('footer.weather'), 'url' => route('weather')],
                    ['label' => $city->name, 'url' => route('weather', ['city' => $city->slug])],
                ]" />

                <div class="mt-10 grid items-center gap-10 lg:mt-14 lg:grid-cols-12">
                    <div class="lg:col-span-7">
                        <p class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-[0.18em] text-[#FF8A8C]">
                            <span class="h-px w-8 bg-[#FF8A8C]"></span>{{ $hero['eyebrow'] }}
                        </p>
                        <h1 class="mt-4 text-[44px] font-extrabold leading-[0.98] tracking-[-0.04em] sm:text-6xl lg:text-7xl">
                            <span class="text-[#FF5A5F]">{{ $city->name }}</span>
                            <span class="block text-white/55">{{ __('weather.page_city_sub') }}</span>
                        </h1>
                        <p class="mt-5 max-w-xl text-base leading-relaxed text-white/70 md:text-lg">{{ $hero['description'] }}</p>

                        {{-- şehir seçimi: sitenin ortak şehir seçicisi (seçim diğer sayfalarla ortak) --}}
                        @if ($cities->count() > 1)
                            <div class="mt-8 w-full max-w-md">
                                @include('partials.city-picker', [
                                    'cities' => $cities, 'current' => $city, 'label' => __('weather.choose_city'),
                                    'url' => fn ($c) => route('weather', ['city' => $c->slug]) . '#top',
                                    'meta' => fn ($c) => trim($countryName($c->country) . ' · ' . $forecasts[$c->id]['now']['temp'] . '° ' . __('weather.c_' . $forecasts[$c->id]['now']['condition'])),
                                ])
                            </div>
                        @endif
                    </div>

                    {{-- anlık hava --}}
                    <div class="lg:col-span-5">
                        <div class="rounded-[28px] bg-white/[0.06] p-6 shadow-[0_30px_60px_-30px_rgba(0,0,0,0.5)] ring-1 ring-white/25 backdrop-blur-[3px] sm:p-7">
                            <div class="flex items-center justify-between gap-4">
                                <p class="text-sm font-semibold text-white/70">{{ $flag($city->country?->code) }} {{ $city->name }} · {{ $now->format('H:i') }}</p>
                                <span class="rounded-full bg-white/15 px-2.5 py-1 text-[11px] font-bold uppercase tracking-wider text-white/80">{{ __('weather.now') }}</span>
                            </div>
                            <div class="mt-4 flex items-center gap-5">
                                <x-weather-icon :condition="$forecast['now']['condition']" class="size-24 drop-shadow-[0_10px_30px_rgba(251,191,36,0.3)]" />
                                <div>
                                    <p class="text-7xl font-extrabold leading-none tracking-tighter tabular-nums">{{ $forecast['now']['temp'] }}°</p>
                                    <p class="mt-2 text-lg font-bold text-amber-300">{{ __('weather.c_' . $forecast['now']['condition']) }}</p>
                                </div>
                            </div>
                            <dl class="mt-6 grid grid-cols-3 gap-px overflow-hidden rounded-2xl bg-white/20 text-sm ring-1 ring-white/20">
                                @foreach ([
                                    ['high', $today['max'] . '°'], ['low', $today['min'] . '°'], ['wind', $forecast['now']['wind'] . ' km/h'],
                                    ['humidity', '%' . $forecast['now']['humidity']], ['sunrise', $sun['sunrise']], ['sunset', $sun['sunset']],
                                ] as [$label, $value])
                                    <div class="bg-slate-950/25 px-3.5 py-3">
                                        <dt class="text-[10px] font-semibold uppercase tracking-[0.14em] text-white/50">{{ __('weather.' . $label) }}</dt>
                                        <dd class="mt-0.5 font-bold tabular-nums">{{ $value ?? '—' }}</dd>
                                    </div>
                                @endforeach
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- SAATLİK --}}
        @if ($hours->isNotEmpty())
            <section class="border-b border-slate-200 bg-white py-10 md:py-12">
                <div class="mx-auto max-w-7xl px-4">
                    <h2 class="text-[11px] font-bold uppercase tracking-[0.2em] text-[#C62E2E]">{{ __('weather.hourly') }}</h2>
                    <ul class="mt-5 flex snap-x gap-2.5 overflow-x-auto pb-2 [scrollbar-width:thin]">
                        @foreach ($hours as $hour)
                            <li @class(['flex w-20 shrink-0 snap-start flex-col items-center rounded-2xl px-2 py-3.5 text-center',
                                'bg-slate-950 text-white' => $loop->first, 'bg-slate-50 ring-1 ring-slate-200/70' => !$loop->first])>
                                <span @class(['text-xs font-bold tabular-nums', 'text-amber-300' => $loop->first, 'text-slate-500' => !$loop->first])>{{ $loop->first ? __('weather.now') : substr($hour['time'], 11, 5) }}</span>
                                <x-weather-icon :condition="$hour['condition']" class="my-2 size-9" />
                                <span class="text-base font-extrabold tabular-nums">{{ $hour['temp'] }}°</span>
                                <span @class(['mt-0.5 h-4 text-[10px] font-semibold tabular-nums', 'text-sky-300' => $loop->first, 'text-sky-600' => !$loop->first])>{{ $hour['rain'] >= 0.1 ? $mm($hour['rain']) . ' mm' : '' }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </section>
        @endif

        {{-- BUGÜN: gün parçaları + ne giysem + günün en iyi saatleri --}}
        <section class="bg-slate-50 py-14 md:py-20">
            <div class="mx-auto max-w-7xl px-4">
                <p class="inline-flex items-center gap-2 text-[11px] font-bold uppercase tracking-[0.2em] text-[#C62E2E]">
                    <span class="h-px w-6 bg-[#C62E2E]"></span>{{ __('weather.today') }} · {{ $now->copy()->locale($locale)->isoFormat('D MMMM') }}
                </p>
                <h2 class="mt-3 text-3xl font-extrabold tracking-[-0.025em] text-slate-900 sm:text-4xl">{{ __('weather.today_title') }}</h2>

                <div class="mt-8 grid gap-6 lg:grid-cols-12">
                    @if ($parts)
                        <div @class(['grid grid-cols-2 gap-px overflow-hidden rounded-[28px] bg-slate-200/70 ring-1 ring-slate-200/80 sm:grid-cols-4', 'lg:col-span-7' => $tips, 'lg:col-span-12' => !$tips])>
                            @foreach ($parts as $part)
                                @php
                                    // gün parçasının rengi: sabah altın, öğleden sonra turuncu, akşam kırmızı, gece lacivert
                                    $night = $part['part'] === 'night';
                                    $partBg = ['morning' => 'bg-gradient-to-b from-amber-100/80 to-white', 'afternoon' => 'bg-gradient-to-b from-orange-100/70 to-white', 'evening' => 'bg-gradient-to-b from-[#C62E2E]/[0.12] to-white', 'night' => 'bg-gradient-to-b from-slate-900 to-slate-950 text-white'][$part['part']];
                                @endphp
                                <div class="flex flex-col items-center px-3 py-6 text-center sm:px-4 {{ $partBg }}">
                                    <p @class(['text-[11px] font-bold uppercase tracking-[0.14em]', 'text-amber-300' => $night, 'text-[#C62E2E]' => !$night && $loop->first, 'text-slate-500' => !$night && !$loop->first])>
                                        @if ($part['tomorrow']){{ __('weather.tomorrow') }} · @endif{{ __('weather.part_' . $part['part']) }}
                                    </p>
                                    <p @class(['mt-0.5 text-[11px] tabular-nums', 'text-white/45' => $night, 'text-slate-400' => !$night])>{{ $part['from'] }}–{{ $part['to'] }}</p>
                                    <x-weather-icon :condition="$part['condition']" class="my-4 size-14" />
                                    <p @class(['text-3xl font-extrabold tracking-tight tabular-nums', 'text-white' => $night, 'text-slate-900' => !$night])>{{ $part['temp'] }}°</p>
                                    <p @class(['mt-1 text-sm font-medium', 'text-white/60' => $night, 'text-slate-500' => !$night])>{{ __('weather.c_' . $part['condition']) }}</p>
                                    <p class="mt-4 flex flex-wrap justify-center gap-1.5">
                                        @if ($part['rain'] >= 0.2)
                                            <span class="inline-flex items-center gap-1 rounded-full bg-sky-50 px-2.5 py-1 text-[11px] font-bold text-sky-700 tabular-nums">
                                                <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3s6 6.5 6 11a6 6 0 0 1-12 0c0-4.5 6-11 6-11Z"/></svg>{{ $mm($part['rain']) }} mm
                                            </span>
                                        @endif
                                        <span @class(['inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-[11px] font-bold tabular-nums', 'bg-white/10 text-white/80' => $night, 'bg-white text-[#C62E2E] ring-1 ring-[#C62E2E]/15' => !$night && $part['wind'] >= 30, 'bg-white text-slate-600 ring-1 ring-slate-200' => !$night && $part['wind'] < 30])>
                                            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 8h10a3 3 0 1 0-3-3M3 12h15a3 3 0 1 1-3 3M3 16h7"/></svg>{{ $part['wind'] }} km/h
                                        </span>
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    @endif
                    @if ($tips)
                        <div @class(['relative isolate overflow-hidden rounded-[28px] bg-gradient-to-br from-[#C62E2E]/[0.08] via-white to-amber-50 p-6 ring-1 ring-[#C62E2E]/10 sm:p-7', 'lg:col-span-5' => $parts, 'lg:col-span-12' => !$parts])>
                            <svg class="pointer-events-none absolute -bottom-8 -right-8 -z-10 size-40 text-[#C62E2E] opacity-[0.06] rtl:-left-8 rtl:right-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M8 3 3 6l2 4 3-1v12h8V9l3 1 2-4-5-3a4 4 0 0 1-8 0Z"/></svg>
                            <p class="flex items-center gap-2 text-[11px] font-bold uppercase tracking-[0.18em] text-[#C62E2E]">
                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 3 3 6l2 4 3-1v12h8V9l3 1 2-4-5-3a4 4 0 0 1-8 0Z"/></svg>
                                {{ __('weather.wear_title') }}
                            </p>
                            <x-weather-tips :tips="$tips" :dark="false" class="mt-2" />
                        </div>
                    @endif
                </div>

                <h3 class="mt-12 text-xl font-extrabold tracking-tight text-slate-900">{{ __('weather.best_title') }}</h3>
                <x-weather-best-times :city="$city" :forecast="$forecast" :dark="false" class="mt-4" />
            </div>
        </section>

        {{-- AYRINTILAR: hissedilen, UV, rüzgâr (yön), nem, basınç, bulut — MET "complete" verisi; eski önbellekte yoksa kart çıkmaz --}}
        @php
            $tileClass = 'flex flex-col rounded-3xl p-6 ring-1 ring-slate-200/70';
            // kart zeminleri: sitenin altın / kırmızı / mavi tonları, hafif
            $tint = ['feels' => 'bg-gradient-to-br from-amber-50 to-white', 'uv' => 'bg-gradient-to-br from-amber-100/70 to-white', 'wind' => 'bg-gradient-to-br from-[#C62E2E]/[0.07] to-white', 'humidity' => 'bg-gradient-to-br from-sky-50 to-white', 'pressure' => 'bg-gradient-to-br from-slate-100 to-white', 'clouds' => 'bg-gradient-to-br from-sky-50 via-slate-50 to-white'];
            $afterSunset = $sunDay['set'] && time() > $sunDay['set'];
            $uvDay = $afterSunset ? ($days[1] ?? $days[0]) : $days[0];
            $uv = $uvDay['uv'] ?? null;
            $uvLevel = $uv !== null ? \App\Support\Weather::uvLevel($uv) : null;
            $windLevel = \App\Support\Weather::windLevel($current['wind']);
            $dew = $current['dew'] ?? null;
            $comfort = $dew === null ? null : ($dew < 10 ? 'humid_dry' : ($dew < 16 ? 'humid_ok' : ($dew < 20 ? 'humid_sticky' : 'humid_muggy')));
            $pressure = $current['pressure'] ?? null;
            $cloud = $current['cloud'] ?? null;
            $feels = $current['feels'] ?? null;
            $bar = fn ($value, $min, $max) => max(0, min(100, round(($value - $min) / ($max - $min) * 100)));
        @endphp
        <section class="bg-white py-14 md:py-20">
            <div class="mx-auto max-w-7xl px-4">
                <p class="inline-flex items-center gap-2 text-[11px] font-bold uppercase tracking-[0.2em] text-[#C62E2E]">
                    <span class="h-px w-6 bg-[#C62E2E]"></span>{{ __('weather.now_at', ['date' => $now->copy()->locale($locale)->isoFormat('D MMMM dddd'), 'time' => $now->format('H:i')]) }}
                </p>
                <h2 class="mt-3 text-3xl font-extrabold tracking-[-0.025em] text-slate-900 sm:text-4xl">{!! __('weather.details_title', ['city' => '<span class="text-[#C62E2E]">' . e($city->name) . '</span>']) !!}</h2>

                <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    {{-- hissedilen --}}
                    @if ($feels !== null)
                        <div class="{{ $tileClass }} {{ $tint['feels'] }}">
                            <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-slate-400">{{ __('weather.feels') }}</p>
                            <p class="mt-3 text-5xl font-extrabold tracking-tighter text-slate-900 tabular-nums">{{ $feels }}°</p>
                            <div class="relative mt-5 h-2 rounded-full bg-gradient-to-r from-sky-400 via-amber-300 to-[#C62E2E]">
                                <span class="absolute top-1/2 size-4 -translate-x-1/2 -translate-y-1/2 rounded-full border-[3px] border-white bg-slate-900 shadow" style="left: {{ $bar($feels, -10, 40) }}%"></span>
                            </div>
                            <p class="mt-4 text-sm text-slate-500"><strong class="font-semibold text-slate-700">{{ __('weather.actual', ['temp' => $current['temp']]) }}</strong> · {{ __($feels <= $current['temp'] - 2 ? 'weather.feels_colder' : ($feels >= $current['temp'] + 2 ? 'weather.feels_warmer' : 'weather.feels_same')) }}</p>
                        </div>
                    @endif

                    {{-- UV --}}
                    @if ($uv !== null)
                        <div class="{{ $tileClass }} {{ $tint['uv'] }}">
                            <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-slate-400">{{ __('weather.uv') }} · {{ __($afterSunset ? 'weather.uv_peak_tomorrow' : 'weather.uv_peak') }}</p>
                            <p class="mt-3 flex items-baseline gap-3">
                                <span class="text-5xl font-extrabold tracking-tighter text-slate-900 tabular-nums">{{ (int) round($uv) }}</span>
                                <span @class(['rounded-full px-2.5 py-1 text-xs font-bold',
                                    'bg-emerald-50 text-emerald-700' => $uvLevel === 'low',
                                    'bg-amber-50 text-amber-700' => $uvLevel === 'moderate',
                                    'bg-orange-50 text-orange-700' => $uvLevel === 'high',
                                    'bg-[#C62E2E]/10 text-[#C62E2E]' => in_array($uvLevel, ['very_high', 'extreme'])])>{{ __('weather.uv_' . $uvLevel) }}</span>
                            </p>
                            <div class="relative mt-5 h-2 rounded-full bg-gradient-to-r from-emerald-400 via-amber-300 via-60% to-[#C62E2E]">
                                <span class="absolute top-1/2 size-4 -translate-x-1/2 -translate-y-1/2 rounded-full border-[3px] border-white bg-slate-900 shadow" style="left: {{ $bar($uv, 0, 11) }}%"></span>
                            </div>
                            <p class="mt-4 text-sm text-slate-500">{{ __($uvLevel === 'low' ? 'weather.uv_tip_low' : ($uvLevel === 'moderate' ? 'weather.uv_tip_mid' : 'weather.uv_tip_high')) }}</p>
                        </div>
                    @endif

                    {{-- rüzgâr + yön --}}
                    <div class="{{ $tileClass }} {{ $tint['wind'] }}">
                        <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-slate-400">{{ __('weather.wind') }}</p>
                        <div class="mt-3 flex items-center justify-between gap-4">
                            <p>
                                <span class="text-5xl font-extrabold tracking-tighter text-slate-900 tabular-nums">{{ $current['wind'] }}</span>
                                <span class="text-sm font-bold text-slate-400">km/h</span>
                            </p>
                            @if (($current['dir'] ?? null) !== null)
                                {{-- pusula: ok rüzgârın gittiği yönü gösterir --}}
                                <svg class="size-16 shrink-0" viewBox="0 0 64 64" aria-hidden="true">
                                    <circle cx="32" cy="32" r="29" fill="white" stroke="#e2e8f0" stroke-width="2"/>
                                    @foreach ([0, 90, 180, 270] as $deg)
                                        <line x1="32" y1="5" x2="32" y2="10" stroke="#cbd5e1" stroke-width="2" stroke-linecap="round" transform="rotate({{ $deg }} 32 32)"/>
                                    @endforeach
                                    <text x="32" y="19" text-anchor="middle" font-size="8" font-weight="800" fill="#94a3b8">N</text>
                                    <g transform="rotate({{ round($current['dir'] + 180) }} 32 32)">
                                        <path d="M32 14 39 34H25Z" fill="#C62E2E"/>
                                        <rect x="30" y="33" width="4" height="15" rx="2" fill="#C62E2E"/>
                                    </g>
                                </svg>
                            @endif
                        </div>
                        <p class="mt-4 text-sm text-slate-500">
                            <strong @class(['font-semibold', 'text-[#C62E2E]' => $current['wind'] >= 30, 'text-slate-700' => $current['wind'] < 30])>{{ __('weather.wind_' . $windLevel) }}</strong>
                            @if (($current['dir'] ?? null) !== null) · {{ __('weather.wind_dir', ['dir' => __('weather.dir_' . \App\Support\Weather::direction($current['dir']))]) }}@endif
                        </p>
                    </div>

                    {{-- nem + çiy noktası --}}
                    <div class="{{ $tileClass }} {{ $tint['humidity'] }}">
                        <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-slate-400">{{ __('weather.humidity') }}</p>
                        <p class="mt-3 text-5xl font-extrabold tracking-tighter text-slate-900 tabular-nums">%{{ $current['humidity'] }}</p>
                        <div class="mt-5 h-2 overflow-hidden rounded-full bg-slate-200">
                            <span class="block h-full rounded-full bg-gradient-to-r from-sky-300 to-sky-600" style="width: {{ $current['humidity'] }}%"></span>
                        </div>
                        @if ($comfort)
                            <p class="mt-4 text-sm text-slate-500"><strong class="font-semibold text-slate-700">{{ __('weather.' . $comfort) }}</strong> · {{ __('weather.dew_point', ['temp' => $dew]) }}</p>
                        @endif
                    </div>

                    {{-- basınç --}}
                    @if ($pressure !== null)
                        <div class="{{ $tileClass }} {{ $tint['pressure'] }}">
                            <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-slate-400">{{ __('weather.pressure') }}</p>
                            <p class="mt-3">
                                <span class="text-5xl font-extrabold tracking-tighter text-slate-900 tabular-nums">{{ $pressure }}</span>
                                <span class="text-sm font-bold text-slate-400">hPa</span>
                            </p>
                            <div class="relative mt-5 h-2 rounded-full bg-gradient-to-r from-slate-300 via-slate-200 to-slate-300">
                                <span class="absolute inset-y-0 left-[41.6%] w-[25%] rounded-full bg-emerald-200"></span>
                                <span class="absolute top-1/2 size-4 -translate-x-1/2 -translate-y-1/2 rounded-full border-[3px] border-white bg-slate-900 shadow" style="left: {{ $bar($pressure, 980, 1040) }}%"></span>
                            </div>
                            <p class="mt-4 text-sm text-slate-500">{{ __($pressure < 1005 ? 'weather.pressure_low' : ($pressure > 1020 ? 'weather.pressure_high' : 'weather.pressure_normal')) }}</p>
                        </div>
                    @endif

                    {{-- bulut --}}
                    @if ($cloud !== null)
                        <div class="{{ $tileClass }} {{ $tint['clouds'] }}">
                            <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-slate-400">{{ __('weather.clouds') }}</p>
                            <p class="mt-3 text-5xl font-extrabold tracking-tighter text-slate-900 tabular-nums">%{{ $cloud }}</p>
                            <div class="mt-5 h-2 overflow-hidden rounded-full bg-sky-100">
                                <span class="block h-full rounded-full bg-gradient-to-r from-slate-300 to-slate-500" style="width: {{ $cloud }}%"></span>
                            </div>
                            <p class="mt-4 text-sm text-slate-500">{{ __($cloud < 25 ? 'weather.clouds_clear' : ($cloud < 70 ? 'weather.clouds_partly' : 'weather.clouds_over')) }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </section>

        {{-- 7 GÜN --}}
        <section class="bg-slate-50 py-14 md:py-20">
            <div class="mx-auto max-w-7xl px-4">
                <p class="inline-flex items-center gap-2 text-[11px] font-bold uppercase tracking-[0.2em] text-[#C62E2E]">
                    <span class="h-px w-6 bg-[#C62E2E]"></span>{{ __('weather.forecast') }}
                </p>
                <h2 class="mt-3 text-3xl font-extrabold tracking-[-0.025em] text-slate-900 sm:text-4xl">
                    {!! __('weather.daily_title', ['city' => '<span class="text-[#C62E2E]">' . e($city->name) . '</span>']) !!}
                </h2>

                {{-- her gün bir satır: en düşük – en yüksek sıcaklık haftanın aralığına göre renkli çubukta --}}
                @php
                    $weekMin = $days->min('min');
                    $weekSpan = max(1, $days->max('max') - $weekMin);
                @endphp
                <ul class="mt-8 space-y-2.5">
                    @foreach ($days as $day)
                        @php
                            $d = \Illuminate\Support\Carbon::parse($day['date'], $tz)->locale($locale);
                            $daySun = \App\Support\Weather::sun($city, $day['date']);
                            $first = $loop->first;
                            $left = round(($day['min'] - $weekMin) / $weekSpan * 100);
                            $width = max(6, round(($day['max'] - $day['min']) / $weekSpan * 100));
                        @endphp
                        <li @class(['relative isolate grid grid-cols-[minmax(0,1fr)_auto] items-center gap-x-4 gap-y-3 overflow-hidden rounded-3xl px-5 py-4 md:grid-cols-[9rem_minmax(0,1fr)_auto_minmax(0,15rem)] md:gap-6 md:px-6',
                            'bg-slate-950 text-white shadow-[0_24px_50px_-30px_rgba(15,23,42,0.8)]' => $first,
                            'bg-white ring-1 ring-slate-200/80' => !$first])>
                            @if ($first)
                                <div class="pointer-events-none absolute -left-20 -top-24 -z-10 size-72 rounded-full bg-[radial-gradient(circle,rgba(198,46,46,0.45),transparent_65%)]" aria-hidden="true"></div>
                            @endif
                            {{-- gün --}}
                            <div class="flex min-w-0 items-center gap-3 md:block">
                                <x-weather-icon :condition="$day['condition']" class="size-11 shrink-0 md:hidden" />
                                <div class="min-w-0">
                                    <p @class(['truncate font-extrabold capitalize', 'text-amber-300' => $first, 'text-slate-900' => !$first])>{{ $first ? __('weather.today') : $d->isoFormat('dddd') }}</p>
                                    <p @class(['text-xs', 'text-white/50' => $first, 'text-slate-400' => !$first])>{{ $d->isoFormat('D MMMM') }}</p>
                                </div>
                            </div>
                            {{-- hava --}}
                            <div class="hidden min-w-0 items-center gap-3 md:flex">
                                <x-weather-icon :condition="$day['condition']" class="size-11 shrink-0" />
                                <span @class(['truncate text-sm font-semibold', 'text-white/80' => $first, 'text-slate-600' => !$first])>{{ __('weather.c_' . $day['condition']) }}</span>
                            </div>
                            {{-- yağış / rüzgâr / güneş --}}
                            <div class="col-span-2 flex flex-wrap gap-1.5 md:col-span-1 md:justify-end">
                                @if ($day['rain'] >= 0.5)
                                    <span @class(['inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-[11px] font-bold tabular-nums', 'bg-sky-400/15 text-sky-300' => $first, 'bg-sky-50 text-sky-700' => !$first])>
                                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3s6 6.5 6 11a6 6 0 0 1-12 0c0-4.5 6-11 6-11Z"/></svg>{{ $mm($day['rain']) }} mm
                                    </span>
                                @endif
                                <span @class(['inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-[11px] font-bold tabular-nums',
                                    'bg-white/10 text-white/80' => $first,
                                    'bg-[#C62E2E]/10 text-[#C62E2E]' => !$first && ($day['wind'] ?? 0) >= 30,
                                    'bg-slate-100 text-slate-600' => !$first && ($day['wind'] ?? 0) < 30])>
                                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 8h10a3 3 0 1 0-3-3M3 12h15a3 3 0 1 1-3 3M3 16h7"/></svg>{{ $day['wind'] ?? '—' }} km/h
                                </span>
                                <span @class(['inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-[11px] font-bold tabular-nums', 'bg-white/10 text-white/80' => $first, 'bg-amber-50 text-amber-700' => !$first])>
                                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v3M5.6 7.6l1.4 1.4M18.4 7.6 17 9M3 16h18M7 16a5 5 0 0 1 10 0M8 20h8"/></svg>{{ $daySun['sunrise'] }} – {{ $daySun['sunset'] }}
                                </span>
                            </div>
                            {{-- sıcaklık çubuğu --}}
                            <div class="row-start-1 col-start-2 flex items-center gap-3 tabular-nums md:col-start-4 md:row-start-auto">
                                <span @class(['w-8 text-right text-sm font-semibold', 'text-white/50' => $first, 'text-slate-400' => !$first])>{{ $day['min'] }}°</span>
                                <span @class(['relative hidden h-2 w-28 rounded-full sm:block md:w-full', 'bg-white/10' => $first, 'bg-slate-100' => !$first])>
                                    <span class="absolute inset-y-0 rounded-full bg-gradient-to-r from-amber-300 via-[#FF6B6E] to-[#C62E2E]" style="left: {{ $left }}%; width: {{ min($width, 100 - $left) }}%"></span>
                                </span>
                                <span @class(['w-9 text-lg font-extrabold', 'text-white' => $first, 'text-slate-900' => !$first])>{{ $day['max'] }}°</span>
                            </div>
                        </li>
                    @endforeach
                </ul>

                {{-- bu havada ne yapılır --}}
                <div class="mt-6 grid gap-6 lg:grid-cols-12">
                    @if ($suggestions->isNotEmpty())
                        <div class="rounded-[28px] bg-white p-6 ring-1 ring-slate-200/80 sm:p-7 lg:col-span-12">
                            <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-[#C62E2E]">{{ __($wet ? 'weather.suggest_wet' : 'weather.suggest_dry') }}</p>
                            <ul class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                                @foreach ($suggestions as $activity)
                                    <li>
                                        <a href="{{ route('activities.show', ['slug' => $activity->slug]) }}" class="group flex items-center gap-3 rounded-2xl p-2 transition hover:bg-slate-50">
                                            @if ($cover = $activity->cover?->url('sm'))
                                                <img src="{{ $cover }}" alt="" loading="lazy" class="size-14 shrink-0 rounded-xl object-cover">
                                            @endif
                                            <span class="line-clamp-2 text-sm font-bold leading-snug text-slate-900 group-hover:text-[#C62E2E]">{{ $activity->name }}</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </div>
        </section>

        {{-- GÜNEŞ VE AY: güneşin gün içindeki yolu, altın / mavi saatler, ayın evresi (hesaplanır, servis yok) --}}
        @if ($sunDay['rise'] && $sunDay['set'])
            @php
                $t = max(0, min(1, (time() - $sunDay['rise']) / ($sunDay['set'] - $sunDay['rise'])));
                $sx = round(200 - 170 * cos(M_PI * $t), 1);
                $sy = round(170 - 140 * sin(M_PI * $t), 1);
                $sunStatus = time() < $sunDay['rise'] ? __('weather.before_sunrise', ['time' => $duration($sunDay['rise'] - time())])
                    : (time() > $sunDay['set'] ? __('weather.sun_down') : __('weather.daylight_left', ['time' => $duration($sunDay['set'] - time())]));
                // ay: aydınlık kısım (sağ: büyüyen, sol: küçülen)
                $k = cos(2 * M_PI * $moon['fraction']);
                $rx = round(abs($k) * 50, 2);
                $moonPath = $moon['fraction'] < 0.5
                    ? "M50 0A50 50 0 0 1 50 100A{$rx} 50 0 0 " . ($k < 0 ? 1 : 0) . ' 50 0Z'
                    : "M50 0A50 50 0 0 0 50 100A{$rx} 50 0 0 " . ($k < 0 ? 0 : 1) . ' 50 0Z';
                $sunRows = array_filter([
                    ['golden_morning', $sunDay['golden_morning'], 'bg-amber-400'],
                    ['golden_evening', $sunDay['golden_evening'], 'bg-amber-400'],
                    ['blue_morning', $sunDay['blue_morning'], 'bg-sky-500'],
                    ['blue_evening', $sunDay['blue_evening'], 'bg-sky-500'],
                ], fn ($row) => $row[1]);
            @endphp
            <section class="bg-white py-14 md:py-20">
                <div class="mx-auto max-w-7xl px-4">
                    <p class="inline-flex items-center gap-2 text-[11px] font-bold uppercase tracking-[0.2em] text-[#C62E2E]">
                        <span class="h-px w-6 bg-[#C62E2E]"></span>{{ __('weather.sun_eyebrow') }} · {{ $now->copy()->locale($locale)->isoFormat('D MMMM') }}
                    </p>
                    <h2 class="mt-3 text-3xl font-extrabold tracking-[-0.025em] text-slate-900 sm:text-4xl">{{ __('weather.sun_title') }}</h2>

                    <div class="mt-8 grid gap-6 lg:grid-cols-12">
                        <div class="rounded-[28px] bg-slate-50 p-6 ring-1 ring-slate-200/70 sm:p-8 lg:col-span-8">
                            <svg viewBox="0 0 400 190" class="mx-auto w-full max-w-lg" aria-hidden="true">
                                <defs>
                                    <linearGradient id="sunPath" x1="0" x2="1">
                                        <stop offset="0" stop-color="#fbbf24"/><stop offset="1" stop-color="#C62E2E"/>
                                    </linearGradient>
                                    <radialGradient id="sunGlow"><stop offset="0" stop-color="#fbbf24" stop-opacity=".45"/><stop offset="1" stop-color="#fbbf24" stop-opacity="0"/></radialGradient>
                                </defs>
                                <line x1="10" y1="170" x2="390" y2="170" stroke="#cbd5e1" stroke-width="2"/>
                                <path d="M30 170A170 140 0 0 1 370 170" fill="none" stroke="#cbd5e1" stroke-width="2.5" stroke-dasharray="3 7" stroke-linecap="round"/>
                                @if ($t > 0)
                                    <path d="M30 170A170 140 0 0 1 {{ $sx }} {{ $sy }}" fill="none" stroke="url(#sunPath)" stroke-width="4" stroke-linecap="round"/>
                                @endif
                                <circle cx="{{ $sx }}" cy="{{ $sy }}" r="30" fill="url(#sunGlow)"/>
                                <circle cx="{{ $sx }}" cy="{{ $sy }}" r="12" fill="#fbbf24" stroke="white" stroke-width="4"/>
                            </svg>
                            <div class="mx-auto -mt-2 grid max-w-lg grid-cols-3 text-center">
                                @foreach ([['sunrise', $sunDay['sunrise']], ['solar_noon', $sunDay['noon']], ['sunset', $sunDay['sunset']]] as [$label, $value])
                                    <div @class(['text-left rtl:text-right' => $loop->first, 'text-right rtl:text-left' => $loop->last])>
                                        <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-slate-400">{{ __('weather.' . $label) }}</p>
                                        <p class="mt-1 text-2xl font-extrabold text-slate-900 tabular-nums">{{ $value }}</p>
                                    </div>
                                @endforeach
                            </div>
                            <p class="mt-6 inline-flex items-center gap-2 rounded-full bg-white px-3.5 py-1.5 text-sm font-semibold text-slate-700 ring-1 ring-slate-200">
                                <span class="size-2 rounded-full bg-amber-400"></span>{{ $sunStatus }}
                                @if ($sunDay['length']) <span class="text-slate-300">·</span> {{ __('weather.day_length') }}: {{ $duration($sunDay['length']) }}@endif
                            </p>
                            <ul class="mt-6 grid gap-x-8 gap-y-3 border-t border-slate-200 pt-6 sm:grid-cols-2">
                                @foreach ($sunRows as [$label, $range, $dot])
                                    <li class="flex items-center justify-between gap-3 text-sm">
                                        <span class="flex items-center gap-2.5 font-semibold text-slate-700"><span class="size-2.5 rounded-full {{ $dot }}"></span>{{ __('weather.' . $label) }}</span>
                                        <span class="font-bold text-slate-900 tabular-nums">{{ $range[0] }} – {{ $range[1] }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        {{-- ay --}}
                        <div class="relative isolate flex flex-col items-center justify-center overflow-hidden rounded-[28px] bg-slate-950 p-8 text-center text-white lg:col-span-4">
                            <div class="pointer-events-none absolute inset-0 -z-10 opacity-[0.07] [background-image:radial-gradient(rgba(255,255,255,.9)_1px,transparent_1px)] [background-size:22px_22px]" aria-hidden="true"></div>
                            <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-amber-300">{{ __('weather.moon') }}</p>
                            <svg viewBox="0 0 100 100" class="mt-6 size-36 drop-shadow-[0_0_30px_rgba(255,255,255,0.18)]" aria-hidden="true">
                                <circle cx="50" cy="50" r="50" fill="#1e293b"/>
                                <path d="{{ $moonPath }}" fill="#f1f5f9"/>
                            </svg>
                            <p class="mt-6 text-2xl font-extrabold">{{ __('weather.moon_' . $moon['phase']) }}</p>
                            <p class="mt-1 text-sm text-white/60">{{ __('weather.moon_lit', ['percent' => $moon['lit']]) }}</p>
                            <p class="mt-5 rounded-full bg-white/10 px-3.5 py-1.5 text-xs font-semibold text-white/80">{{ __('weather.next_full', ['date' => $moon['next_full']->copy()->setTimezone($tz)->locale($locale)->isoFormat('D MMMM')]) }}</p>
                        </div>
                    </div>
                </div>
            </section>
        @endif

        {{-- ŞEHİRLER: liste, arama, ülke filtresi --}}
        @if ($cities->count() > 1)
            <section class="bg-slate-50 py-14 md:py-20" x-data="{ q: '', country: '' }">
                <div class="mx-auto max-w-7xl px-4">
                    <div class="flex flex-col gap-5 md:flex-row md:items-end md:justify-between">
                        <div>
                            <p class="inline-flex items-center gap-2 text-[11px] font-bold uppercase tracking-[0.2em] text-[#C62E2E]">
                                <span class="h-px w-6 bg-[#C62E2E]"></span>{{ __('footer.weather') }}
                            </p>
                            <h2 class="mt-3 text-3xl font-extrabold tracking-[-0.025em] text-slate-900 sm:text-4xl">{{ __('weather.cities_title') }}</h2>
                        </div>
                        <div class="flex w-full flex-col gap-2 sm:flex-row md:w-auto">
                            @if ($countries->count() > 1)
                                <select x-model="country" aria-label="{{ __('weather.all_countries') }}"
                                    class="rounded-2xl border-0 bg-slate-50 py-3 pl-4 pr-9 text-sm font-semibold text-slate-900 ring-1 ring-slate-200 focus:ring-2 focus:ring-[#C62E2E] sm:w-56">
                                    <option value="">{{ __('weather.all_countries') }}</option>
                                    @foreach ($countries as $country)
                                        <option value="{{ $country->code }}">{{ $flag($country->code) }} {{ $countryName($country) }}</option>
                                    @endforeach
                                </select>
                            @endif
                            <label class="relative sm:w-64">
                                <span class="sr-only">{{ __('weather.search') }}</span>
                                <svg class="pointer-events-none absolute left-4 top-1/2 size-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg>
                                <input type="search" x-model="q" placeholder="{{ __('weather.search') }}" autocomplete="off"
                                    class="block w-full rounded-2xl border-0 bg-slate-50 py-3 pl-11 pr-4 text-sm ring-1 ring-slate-200 placeholder:text-slate-400 focus:ring-2 focus:ring-[#C62E2E]">
                            </label>
                        </div>
                    </div>

                    <ul class="mt-8 grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
                        @foreach ($cities as $item)
                            @php
                                $f = $forecasts[$item->id];
                                $itemToday = collect($f['days'])->firstWhere('date', \Illuminate\Support\Carbon::now($f['tz'])->toDateString()) ?? $f['days'][0];
                                $selected = $item->is($city);
                            @endphp
                            <li x-show="(!country || country === @js($item->country?->code)) && (!q || @js(mb_strtolower($item->name . ' ' . $countryName($item->country))).includes(q.toLowerCase()))">
                                <a href="{{ route('weather', ['city' => $item->slug]) }}#top"
                                    @class(['group relative isolate flex h-full flex-col overflow-hidden rounded-3xl p-4 transition sm:p-5',
                                        'bg-slate-950 text-white shadow-[0_24px_50px_-30px_rgba(15,23,42,0.8)]' => $selected,
                                        'bg-white ring-1 ring-slate-200/80 hover:ring-[#C62E2E]/40 hover:shadow-[0_20px_44px_-30px_rgba(198,46,46,0.55)]' => !$selected])>
                                    @if ($selected)
                                        <span class="pointer-events-none absolute -right-16 -top-20 -z-10 size-56 rounded-full bg-[radial-gradient(circle,rgba(198,46,46,0.5),transparent_65%)]" aria-hidden="true"></span>
                                    @endif
                                    <span class="flex items-start justify-between gap-3">
                                        <span class="min-w-0">
                                            <span @class(['block truncate text-base font-extrabold sm:text-lg', 'text-white' => $selected, 'text-slate-900 group-hover:text-[#C62E2E]' => !$selected])>{{ $flag($item->country?->code) }} {{ $item->name }}</span>
                                            <span @class(['block truncate text-xs', 'text-white/50' => $selected, 'text-slate-400' => !$selected])>{{ $countryName($item->country) }} · {{ \Illuminate\Support\Carbon::now($f['tz'])->format('H:i') }}</span>
                                        </span>
                                        <span aria-hidden="true" @class(['mt-1 hidden transition-transform group-hover:translate-x-1 sm:inline rtl:rotate-180', 'text-amber-300' => $selected, 'text-[#C62E2E]' => !$selected])>→</span>
                                    </span>
                                    <span class="mt-4 flex items-center justify-between gap-2 sm:mt-5 sm:gap-3">
                                        <span @class(['text-4xl font-extrabold leading-none tracking-tighter tabular-nums sm:text-5xl', 'text-white' => $selected, 'text-slate-900' => !$selected])>{{ $f['now']['temp'] }}°</span>
                                        <x-weather-icon :condition="$f['now']['condition']" class="size-10 shrink-0 sm:size-14" />
                                    </span>
                                    <span @class(['mt-4 flex flex-col gap-0.5 border-t pt-3 text-xs sm:flex-row sm:items-center sm:justify-between sm:gap-3', 'border-white/10' => $selected, 'border-slate-100' => !$selected])>
                                        <span @class(['truncate font-semibold', 'text-amber-300' => $selected, 'text-slate-600' => !$selected])>{{ __('weather.c_' . $f['now']['condition']) }}</span>
                                        <span @class(['shrink-0 tabular-nums', 'text-white/60' => $selected, 'text-slate-400' => !$selected])>↑ {{ $itemToday['max'] }}° · ↓ {{ $itemToday['min'] }}°</span>
                                    </span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </section>
        @endif

        <p class="bg-slate-50 pb-10 text-center text-xs text-slate-400">
            {{ __('weather.updated', ['time' => \Illuminate\Support\Carbon::createFromTimestamp($forecast['updated'], $tz)->format('H:i')]) }}
            · <a href="https://www.met.no/en" target="_blank" rel="noopener nofollow" class="hover:underline">{{ __('weather.source') }}: MET Norway</a>
        </p>
    @endif

    {{-- yapısal veri: seçili şehrin hava durumu sayfası; konusu şehir (yer, koordinat, ülke) --}}
    @if ($city && $forecast)
        @jsonld(\App\Support\Schema::webPage('WebPage', __('weather.page_city_title', ['city' => $city->name]), $hero['description'], [
            'dateModified' => \Illuminate\Support\Carbon::createFromTimestamp($forecast['updated'])->toAtomString(),
            'about' => array_filter([
                '@type' => 'City',
                'name' => $city->name,
                'url' => route('cities.show', ['slug' => $city->slug]),
                'geo' => ['@type' => 'GeoCoordinates', 'latitude' => (float) $city->latitude, 'longitude' => (float) $city->longitude],
                'containedInPlace' => $city->country ? ['@type' => 'Country', 'name' => $city->country->name] : null,
            ]),
            'primaryImageOfPage' => $banner['desktop'] ?? null,
        ]))
    @endif

    <x-page-sections page="weather" />
@endsection
