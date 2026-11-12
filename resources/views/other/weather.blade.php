@extends('layouts.app')

@section('title', $city ? __('weather.page_city_title', ['city' => $city->name]) : __('weather.page_meta_title'))
@section('meta_description', __('weather.page_meta_description'))

{{--
    Hava durumu (/weather?city=…): seçili şehrin detaylı tahmini ve bütün şehirlerin listesi.
    Üst: şehrin fotoğrafı (Sayfalar › Hava durumu › Banner, yoksa şehrin banner'ı) üzerinde başlık + şehir seçimi, sağda anlık hava.
    Sonra: saatlik şerit, 7 günün ayrıntısı, ne giysem + bu havada ne yapılır, şehir listesi (arama, ülke filtresi; tek ülkede filtre yok).
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

        {{-- BUGÜN NE GİYSEM: bugünün havasına göre öneriler (App\Support\Weather::tips), üstte ve renkli kartlarla --}}
        @if ($tips)
            @php
                $tipLook = [
                    'tip_snow' => ['from-sky-400 to-indigo-600', 'bg-sky-50', '<path d="M12 2v20M4.9 4.9l14.2 14.2M2 12h20M4.9 19.1 19.1 4.9"/><path d="m9 4 3 3 3-3M9 20l3-3 3 3"/>'],
                    'tip_dry' => ['from-yellow-400 to-amber-500', 'bg-yellow-50', '<circle cx="6.5" cy="14" r="3.5"/><circle cx="17.5" cy="14" r="3.5"/><path d="M10 14h4M3 13l2-6h2M21 13l-2-6h-2"/>'],
                    'tip_umbrella' => ['from-sky-500 to-blue-700', 'bg-blue-50', '<path d="M12 3a9 9 0 0 1 9 9H3a9 9 0 0 1 9-9Z"/><path d="M12 12v7a2 2 0 0 0 4 0"/>'],
                    'tip_hot' => ['from-amber-400 to-orange-600', 'bg-amber-50', '<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/>'],
                    'tip_cold' => ['from-indigo-400 to-violet-700', 'bg-indigo-50', '<path d="M14 14.8V4.5a2 2 0 0 0-4 0v10.3a4 4 0 1 0 4 0Z"/><path d="M12 10v6"/>'],
                    'tip_jacket' => ['from-teal-400 to-emerald-700', 'bg-teal-50', '<path d="M8 3 4 6v15h5v-6M16 3l4 3v15h-5v-6M8 3l4 4 4-4M12 7v14"/>'],
                    'tip_mild' => ['from-emerald-400 to-green-600', 'bg-emerald-50', '<path d="M8 3 3 6l2 4 3-1v12h8V9l3 1 2-4-5-3a4 4 0 0 1-8 0Z"/>'],
                ];
            @endphp
            <section class="bg-white py-10 md:py-14">
                <div class="mx-auto max-w-7xl px-4">
                    <div class="relative isolate overflow-hidden rounded-[32px] bg-gradient-to-br from-amber-50 via-white to-sky-50 p-6 ring-1 ring-slate-200/80 shadow-[0_30px_70px_-50px_rgba(15,23,42,0.45)] sm:p-8 lg:p-10">
                        <div class="pointer-events-none absolute -right-20 -top-24 -z-10 size-72 rounded-full bg-amber-200/40 blur-3xl" aria-hidden="true"></div>
                        <div class="pointer-events-none absolute -bottom-28 -left-16 -z-10 size-72 rounded-full bg-sky-200/50 blur-3xl" aria-hidden="true"></div>

                        <div class="grid gap-8 lg:grid-cols-12 lg:items-center">
                            <div class="lg:col-span-4">
                                <p class="inline-flex items-center gap-2 rounded-full bg-white px-3 py-1 text-[11px] font-bold uppercase tracking-[0.16em] text-[#C62E2E] ring-1 ring-[#C62E2E]/15">
                                    <span class="size-1.5 rounded-full bg-[#C62E2E]"></span>{{ __('weather.today') }} · {{ $now->copy()->locale($locale)->isoFormat('D MMMM') }}
                                </p>
                                <h2 class="mt-4 text-3xl font-extrabold leading-tight tracking-[-0.025em] text-slate-900 sm:text-4xl">{{ __('weather.wear_title') }}</h2>
                                <p class="mt-2 text-sm text-slate-500">{{ __('weather.wear_note') }}</p>
                                <div class="mt-5 inline-flex items-center gap-3 rounded-2xl bg-white/80 py-2 pl-2 pr-5 ring-1 ring-slate-200/80">
                                    <x-weather-icon :condition="$today['condition']" class="size-12" />
                                    <span>
                                        <span class="block text-sm font-bold text-slate-900">{{ $city->name }} · {{ __('weather.c_' . $today['condition']) }}</span>
                                        <span class="block text-sm tabular-nums text-slate-500"><strong class="font-extrabold text-slate-900">{{ $today['max'] }}°</strong>@if ($today['min'] != $today['max']) / {{ $today['min'] }}°@endif</span>
                                    </span>
                                </div>
                            </div>

                            <ul @class(['grid gap-4 lg:col-span-8', 'sm:grid-cols-2' => count($tips) > 1])>
                                @foreach ($tips as $tip)
                                    @php
                                        [$gradient, $soft, $icon] = $tipLook[$tip] ?? $tipLook['tip_mild'];
                                        // "Yağmur bekleniyor: şemsiye…" → kalın başlık + açıklama
                                        $parts = preg_split('/\s*[:：]\s*/u', __('weather.' . $tip), 2);
                                    @endphp
                                    <li class="flex h-full gap-4 rounded-3xl bg-white p-5 ring-1 ring-slate-200/70 shadow-[0_18px_40px_-30px_rgba(15,23,42,0.5)] sm:p-6">
                                        <span class="grid size-14 shrink-0 place-items-center rounded-2xl bg-gradient-to-br {{ $gradient }} text-white shadow-lg">
                                            <svg class="size-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $icon !!}</svg>
                                        </span>
                                        <span class="min-w-0">
                                            @if (count($parts) === 2)
                                                <span class="block text-lg font-extrabold leading-snug text-slate-900">{{ $parts[0] }}</span>
                                                <span class="mt-1 block text-[15px] leading-relaxed text-slate-600">{{ \Illuminate\Support\Str::ucfirst($parts[1]) }}</span>
                                            @else
                                                <span class="block text-[15px] font-semibold leading-relaxed text-slate-800">{{ $parts[0] }}</span>
                                            @endif
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </section>
        @endif

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

        {{-- 7 GÜN --}}
        <section class="bg-slate-50 py-14 md:py-20">
            <div class="mx-auto max-w-7xl px-4">
                <p class="inline-flex items-center gap-2 text-[11px] font-bold uppercase tracking-[0.2em] text-[#C62E2E]">
                    <span class="h-px w-6 bg-[#C62E2E]"></span>{{ __('weather.forecast') }}
                </p>
                <h2 class="mt-3 text-3xl font-extrabold tracking-[-0.025em] text-slate-900 sm:text-4xl">
                    {!! __('weather.daily_title', ['city' => '<span class="text-[#C62E2E]">' . e($city->name) . '</span>']) !!}
                </h2>

                <div class="mt-8 overflow-hidden rounded-[28px] bg-white ring-1 ring-slate-200/80 shadow-[0_24px_60px_-44px_rgba(15,23,42,0.5)]">
                    <div class="hidden grid-cols-[minmax(0,1.3fr)_minmax(0,1.6fr)_6rem_5rem_6rem_7rem] gap-4 border-b border-slate-100 bg-slate-50/70 px-6 py-3 text-[11px] font-bold uppercase tracking-[0.14em] text-slate-400 md:grid">
                        <span>{{ __('weather.col_day') }}</span>
                        <span>{{ __('weather.col_weather') }}</span>
                        <span class="text-right">{{ __('weather.col_temp') }}</span>
                        <span class="text-right">{{ __('weather.col_rain') }}</span>
                        <span class="text-right">{{ __('weather.wind') }}</span>
                        <span class="text-right">{{ __('weather.sunrise') }} – {{ __('weather.sunset') }}</span>
                    </div>
                    <ul class="divide-y divide-slate-100">
                        @foreach ($days as $day)
                            @php
                                $d = \Illuminate\Support\Carbon::parse($day['date'], $tz)->locale($locale);
                                $daySun = \App\Support\Weather::sun($city, $day['date']);
                            @endphp
                            <li @class(['grid grid-cols-[minmax(0,1fr)_auto_auto] items-center gap-4 px-5 py-4 md:grid-cols-[minmax(0,1.3fr)_minmax(0,1.6fr)_6rem_5rem_6rem_7rem] md:px-6', 'bg-[#C62E2E]/[0.04]' => $loop->first])>
                                <span class="min-w-0">
                                    <span @class(['block font-extrabold capitalize', 'text-[#C62E2E]' => $loop->first, 'text-slate-900' => !$loop->first])>{{ $loop->first ? __('weather.today') : $d->isoFormat('dddd') }}</span>
                                    <span class="block text-xs text-slate-400">{{ $d->isoFormat('D MMMM') }}</span>
                                    {{-- telefonda yağış ve rüzgar sütunları yok: günün altında kısa satır --}}
                                    <span class="mt-1 flex flex-wrap gap-x-3 text-[11px] font-semibold tabular-nums text-slate-500 md:hidden">
                                        @if ($day['rain'] >= 0.5)<span class="text-sky-600">{{ $mm($day['rain']) }} mm</span>@endif
                                        <span>{{ __('weather.wind') }} {{ $day['wind'] ?? '—' }} km/h</span>
                                    </span>
                                </span>
                                <span class="flex min-w-0 items-center gap-3">
                                    <x-weather-icon :condition="$day['condition']" class="size-10" />
                                    <span class="hidden truncate text-sm font-medium text-slate-600 sm:inline">{{ __('weather.c_' . $day['condition']) }}</span>
                                </span>
                                <span class="text-right tabular-nums"><strong class="text-lg font-extrabold text-slate-900">{{ $day['max'] }}°</strong> <span class="text-slate-400">{{ $day['min'] }}°</span></span>
                                <span class="hidden text-right text-sm font-semibold tabular-nums md:block {{ $day['rain'] >= 0.5 ? 'text-sky-600' : 'text-slate-300' }}">{{ $day['rain'] >= 0.5 ? $mm($day['rain']) . ' mm' : '—' }}</span>
                                <span class="hidden text-right text-sm font-semibold tabular-nums text-slate-600 md:block">{{ $day['wind'] ?? '—' }} km/h</span>
                                <span class="hidden text-right text-sm tabular-nums text-slate-500 md:block">{{ $daySun['sunrise'] }} – {{ $daySun['sunset'] }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

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

        {{-- ŞEHİRLER: liste, arama, ülke filtresi --}}
        @if ($cities->count() > 1)
            <section class="bg-white py-14 md:py-20" x-data="{ q: '', country: '' }">
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

                    <ul class="mt-8 divide-y divide-slate-100 overflow-hidden rounded-[28px] ring-1 ring-slate-200/80">
                        @foreach ($cities as $item)
                            @php
                                $f = $forecasts[$item->id];
                                $itemToday = collect($f['days'])->firstWhere('date', \Illuminate\Support\Carbon::now($f['tz'])->toDateString()) ?? $f['days'][0];
                                $selected = $item->is($city);
                            @endphp
                            <li x-show="(!country || country === @js($item->country?->code)) && (!q || @js(mb_strtolower($item->name . ' ' . $countryName($item->country))).includes(q.toLowerCase()))">
                                <a href="{{ route('weather', ['city' => $item->slug]) }}#top"
                                    @class(['group grid grid-cols-[minmax(0,1fr)_auto_auto] items-center gap-4 px-5 py-4 transition sm:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto_6rem_auto] sm:px-6',
                                        'bg-[#C62E2E]/[0.05]' => $selected, 'hover:bg-slate-50' => !$selected])>
                                    <span class="flex min-w-0 items-center gap-3">
                                        <span class="text-xl leading-none">{{ $flag($item->country?->code) }}</span>
                                        <span class="min-w-0">
                                            <span @class(['block truncate font-extrabold', 'text-[#C62E2E]' => $selected, 'text-slate-900 group-hover:text-[#C62E2E]' => !$selected])>{{ $item->name }}</span>
                                            <span class="block truncate text-xs text-slate-400">{{ $countryName($item->country) }} · {{ \Illuminate\Support\Carbon::now($f['tz'])->format('H:i') }}</span>
                                        </span>
                                    </span>
                                    <span class="hidden truncate text-sm font-medium text-slate-500 sm:block">{{ __('weather.c_' . $f['now']['condition']) }}</span>
                                    <x-weather-icon :condition="$f['now']['condition']" class="size-9" />
                                    <span class="text-right tabular-nums">
                                        <strong class="text-xl font-extrabold text-slate-900">{{ $f['now']['temp'] }}°</strong>
                                        <span class="hidden text-xs text-slate-400 sm:block">{{ $itemToday['max'] }}° / {{ $itemToday['min'] }}°</span>
                                    </span>
                                    <span aria-hidden="true" class="hidden text-[#C62E2E] transition-transform group-hover:translate-x-1 sm:block rtl:rotate-180">→</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </section>
        @endif

        <p class="bg-white pb-10 text-center text-xs text-slate-400">
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
