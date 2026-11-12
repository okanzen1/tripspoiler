@extends('layouts.app')

@section('title', $city ? __('time.page_city_title', ['city' => $city->name]) : __('time.page_meta_title'))
@section('meta_description', $city ? __('time.page_city_description', ['city' => $city->name]) : __('time.page_meta_description'))

{{--
    Yerel saat (/time?city=…): şehrin fotoğrafı üzerinde canlı dijital saat, saat farkı hesaplayıcı (dünyadaki bütün saat dilimleri),
    ortak saat planlayıcı (iki şehrin 24 saati alt alta, uygun saatler yeşil), şehrin saat bilgileri ve sitedeki şehirlerin saatleri.
    Dış servis yok (resources/js/local-time.js). Yazılar: Sayfalar › Yerel saat › Hero alanı.
--}}
@php
    $locale = app()->getLocale();
    $hero = \App\Support\PageHero::texts('time', [
        'eyebrow' => __('time.page_eyebrow'), 'title' => __('time.page_title'), 'description' => __('time.page_desc'),
    ]);
    $look = \App\Support\PageHero::visual('time', $banner['focus'] ?? null);
    // Karartma panelden (Sayfalar › sayfa › Hero alanı › Görünüm): Az = fotoğraf canlı, sadece yazı tarafı hafif koyu
    $overlayClass = ['soft' => 'from-slate-950/75 via-slate-950/30 to-transparent', 'medium' => 'from-slate-950/90 via-slate-950/60 to-slate-950/20', 'strong' => 'from-slate-950 via-slate-950/85 to-slate-950/45'][$look['overlay']];
    $bottomClass = ['soft' => 'from-slate-950/40', 'medium' => 'from-slate-950/65', 'strong' => 'from-slate-950/80'][$look['overlay']];
    $flag = fn ($code) => $code ? implode('', array_map(fn ($c) => mb_chr(0x1F1E6 + ord($c) - 65), str_split(strtoupper($code)))) : '';
    $countryName = fn ($country) => $country ? (\Locale::getDisplayRegion('-' . $country->code, $locale) ?: $country->name) : '';
    $units = ['h' => __('time.h'), 'm' => __('time.m')];
    if ($city) {
        $now = \Illuminate\Support\Carbon::now($facts['tz']);
        $dayLength = $sun['sunrise'] && $sun['sunset']
            ? (function () use ($sun) { $m = ((int) substr($sun['sunset'], 0, 2) * 60 + (int) substr($sun['sunset'], 3, 2)) - ((int) substr($sun['sunrise'], 0, 2) * 60 + (int) substr($sun['sunrise'], 3, 2)); return intdiv($m, 60) . ' ' . __('time.h') . ' ' . ($m % 60) . ' ' . __('time.m'); })()
            : null;
    }
@endphp

@section('content')
    @if (!$city)
        <section class="bg-slate-950 py-28 text-center text-white">
            <h1 class="text-4xl font-extrabold tracking-tight">{{ $hero['title'] }}</h1>
        </section>
    @else
    <div x-data="{
            locale: @js($locale),
            now: new Date(),
            timer: null,
            init() { this.timer = setInterval(() => this.now = new Date(), 1000) },
            destroy() { clearInterval(this.timer) },
            clock(tz, seconds = false) {
                const p = window.tsTime.parts(tz, this.now);
                return window.tsTime.pad(p.h) + ':' + window.tsTime.pad(p.mi) + (seconds ? ':' + window.tsTime.pad(p.s) : '');
            },
            date(tz) { return window.tsTime.format(this.now, tz, this.locale, { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }) },
            diffLabel(tz, base) {
                const diff = window.tsTime.offset(tz, this.now) - window.tsTime.offset(base, this.now);
                if (diff === 0) return @js(__('time.same'));
                const text = window.tsTime.diffText(diff, @js($units));
                return (diff > 0 ? @js(__('time.ahead')) : @js(__('time.behind'))).replace(':diff', text);
            },
            mine: window.tsTime ? window.tsTime.visitorZone() : 'UTC',
        }">

        {{-- ÜST: şehrin fotoğrafı + canlı dijital saat --}}
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
                    ['label' => __('footer.time'), 'url' => route('time')],
                    ['label' => $city->name, 'url' => route('time', ['city' => $city->slug])],
                ]" />

                <div class="mt-10 grid items-center gap-10 lg:mt-14 lg:grid-cols-12">
                    <div class="lg:col-span-6">
                        <p class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-[0.18em] text-[#FF8A8C]">
                            <span class="h-px w-8 bg-[#FF8A8C]"></span>{{ $hero['eyebrow'] }}
                        </p>
                        <h1 class="mt-4 text-[44px] font-extrabold leading-[0.98] tracking-[-0.04em] sm:text-6xl lg:text-7xl">
                            {{ $city->name }}
                            <span class="block text-white/55">{{ __('time.page_city_sub') }}</span>
                        </h1>
                        <p class="mt-5 max-w-xl text-base leading-relaxed text-white/70 md:text-lg">{{ $hero['description'] }}</p>
                        @if ($cities->count() > 1)
                            <div class="mt-8 w-full max-w-md">
                                @include('partials.city-picker', [
                                    'cities' => $cities, 'current' => $city, 'label' => __('time.choose_city'),
                                    'url' => fn ($c) => route('time', ['city' => $c->slug]) . '#top',
                                    'meta' => fn ($c) => trim($countryName($c->country) . ' · ' . \Illuminate\Support\Carbon::now($cityZones[$c->id])->format('H:i')),
                                ])
                            </div>
                        @endif
                    </div>

                    {{-- dijital saat --}}
                    <div class="lg:col-span-6">
                        <div class="rounded-[32px] bg-white/[0.06] p-6 shadow-[0_30px_60px_-30px_rgba(0,0,0,0.5)] ring-1 ring-white/25 backdrop-blur-[3px] sm:p-8">
                            <div class="flex items-center justify-between gap-3">
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-white/15 px-3 py-1 text-xs font-bold">
                                    <span class="relative flex size-2"><span class="absolute inline-flex size-full animate-ping rounded-full bg-emerald-400 opacity-60"></span><span class="relative inline-flex size-2 rounded-full bg-emerald-400"></span></span>
                                    {{ __('time.live') }}
                                </span>
                                <span class="text-sm font-semibold text-white/65">{{ $flag($city->country?->code) }} {{ str_replace('_', ' ', $facts['tz']) }} · {{ $facts['offset'] }}</span>
                            </div>
                            <p class="mt-6 font-mono font-extrabold leading-none tracking-tight tabular-nums">
                                <span class="text-[72px] sm:text-[104px]" x-text="clock(@js($facts['tz']))">{{ $now->format('H:i') }}</span><span class="text-3xl text-amber-300 sm:text-5xl" x-text="':' + clock(@js($facts['tz']), true).slice(-2)">:{{ $now->format('s') }}</span>
                            </p>
                            <p class="mt-3 text-lg font-semibold capitalize text-amber-300" x-text="date(@js($facts['tz']))">{{ $now->locale($locale)->isoFormat('dddd, D MMMM YYYY') }}</p>
                            <div class="mt-6 flex items-center justify-between gap-4 rounded-2xl bg-slate-950/25 px-5 py-4 ring-1 ring-white/20">
                                <div>
                                    <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-white/45">{{ __('time.your_time') }}</p>
                                    <p class="mt-0.5 text-2xl font-extrabold tabular-nums" x-text="clock(mine)"></p>
                                </div>
                                <p class="text-right text-base font-bold text-white" x-text="diffLabel(@js($facts['tz']), mine)"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- SAAT FARKI HESAPLAYICI + PLANLAYICI --}}
        <section class="bg-slate-50 py-14 md:py-20"
            x-data="{
                zones: @js($zones),
                from: '', to: @js($facts['tz']),
                day: '', time: '',
                init() {
                    this.from = window.tsTime.visitorZone();
                    if (this.from === this.to) this.from = 'Europe/London' === this.to ? 'America/New_York' : 'Europe/London';
                    this.setNow();
                },
                label(id) { const z = this.zones.find((z) => z[0] === id); return z ? z[1] + (z[2] ? ' · ' + z[2] : '') : id.replaceAll('_', ' ') },
                setNow() {
                    const p = window.tsTime.parts(this.from, new Date());
                    this.day = p.y + '-' + window.tsTime.pad(p.mo) + '-' + window.tsTime.pad(p.d);
                    this.time = window.tsTime.pad(p.h) + ':' + window.tsTime.pad(p.mi);
                },
                swap() { const t = this.to; this.to = this.from; this.from = t; this.setNow() },
                get moment() {
                    const [y, mo, d] = (this.day || '').split('-').map(Number), [h, mi] = (this.time || '').split(':').map(Number);
                    return y && !isNaN(h) ? window.tsTime.zoned(this.from, y, mo, d, h, mi || 0) : new Date();
                },
                get result() { return window.tsTime.format(this.moment, this.to, this.locale, { hour: '2-digit', minute: '2-digit', hourCycle: 'h23' }) },
                get resultDay() { return window.tsTime.format(this.moment, this.to, this.locale, { weekday: 'long', day: 'numeric', month: 'long' }) },
                get gap() { return window.tsTime.offset(this.to, this.moment) - window.tsTime.offset(this.from, this.moment) },
                get gapLabel() {
                    if (this.gap === 0) return @js(__('time.same_zone'));
                    return (this.gap > 0 ? '+' : '−') + window.tsTime.diffText(this.gap, @js($units));
                },
                // planlayıcı: ilk şehrin günündeki 24 saat ve ikinci şehirdeki karşılıkları; ikisinde de 08–22 arası uygun
                get hours() {
                    const [y, mo, d] = (this.day || '').split('-').map(Number);
                    if (!y) return [];
                    return Array.from({ length: 24 }, (_, h) => {
                        const at = window.tsTime.zoned(this.from, y, mo, d, h, 0), other = window.tsTime.parts(this.to, at);
                        const shift = Math.round((Date.UTC(other.y, other.mo - 1, other.d) - Date.UTC(y, mo - 1, d)) / 86400000);
                        return { a: h, b: other.h, bm: other.mi, shift: shift, good: h >= 8 && h < 22 && other.h >= 8 && other.h < 22 };
                    });
                },
            }">
            <div class="mx-auto max-w-7xl px-4">
                <p class="inline-flex items-center gap-2 text-[11px] font-bold uppercase tracking-[0.2em] text-[#C62E2E]">
                    <span class="h-px w-6 bg-[#C62E2E]"></span>{{ __('time.converter_eyebrow') }}
                </p>
                <h2 class="mt-3 text-3xl font-extrabold tracking-[-0.025em] text-slate-900 sm:text-4xl">{{ __('time.converter_title') }}</h2>
                <p class="mt-2 max-w-2xl text-slate-600">{{ __('time.converter_desc') }}</p>

                <div class="mt-8 grid gap-6 lg:grid-cols-12">
                    {{-- seçimler --}}
                    <div class="rounded-[28px] bg-white p-5 ring-1 ring-slate-200/80 shadow-[0_24px_60px_-44px_rgba(15,23,42,0.5)] sm:p-7 lg:col-span-7">
                        @foreach (['from', 'to'] as $side)
                            <div x-data="{ open: false, q: '' }" @click.outside="open = false" class="relative">
                                <label class="text-[11px] font-bold uppercase tracking-[0.16em] text-slate-400">{{ __('time.' . $side) }}</label>
                                <button type="button" @click="open = !open; q = ''; $nextTick(() => $refs.search.focus())"
                                    class="mt-1.5 flex w-full items-center justify-between gap-3 rounded-2xl bg-slate-50 px-4 py-3.5 text-left ring-1 ring-slate-200 transition hover:ring-slate-300 rtl:text-right">
                                    <span class="min-w-0">
                                        <span class="block truncate font-bold text-slate-900" x-text="label({{ $side }})"></span>
                                        <span class="block text-xs tabular-nums text-slate-500" x-text="clock({{ $side }})"></span>
                                    </span>
                                    <svg class="size-4 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M6 9l6 6 6-6"/></svg>
                                </button>
                                <div x-show="open" x-cloak x-transition.opacity class="absolute inset-x-0 top-full z-30 mt-2 overflow-hidden rounded-2xl bg-white shadow-2xl ring-1 ring-slate-200">
                                    <div class="border-b border-slate-100 p-2">
                                        <input x-ref="search" x-model="q" type="search" placeholder="{{ __('time.search_zone') }}" autocomplete="off"
                                            class="block w-full rounded-xl border-0 bg-slate-50 px-3.5 py-2.5 text-sm ring-1 ring-slate-200 focus:ring-2 focus:ring-[#C62E2E]">
                                    </div>
                                    <ul class="max-h-72 overflow-y-auto p-1.5">
                                        <template x-for="z in zones.filter((z) => !q || (z[1] + ' ' + z[2] + ' ' + z[0]).toLowerCase().includes(q.toLowerCase())).slice(0, 60)" :key="z[0]">
                                            <li>
                                                <button type="button" @click="{{ $side }} = z[0]; open = false; if ('{{ $side }}' === 'from') setNow()"
                                                    class="flex w-full items-center justify-between gap-3 rounded-xl px-3 py-2 text-left text-sm hover:bg-slate-50 rtl:text-right"
                                                    :class="{{ $side }} === z[0] && 'bg-slate-50 font-bold'">
                                                    <span class="min-w-0 truncate"><span class="font-semibold text-slate-900" x-text="z[1]"></span> <span class="text-slate-400" x-text="z[2]"></span></span>
                                                    <span class="shrink-0 tabular-nums text-slate-500" x-text="clock(z[0])"></span>
                                                </button>
                                            </li>
                                        </template>
                                    </ul>
                                </div>
                            </div>
                            @if ($side === 'from')
                                <div class="relative z-10 -my-2 flex justify-center">
                                    <button type="button" @click="swap()" aria-label="{{ __('time.swap') }}"
                                        class="mt-3 grid size-10 place-items-center rounded-full bg-slate-900 text-white shadow ring-4 ring-white transition hover:rotate-180 hover:bg-[#C62E2E]">
                                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 4v15m0 0-3.5-3.5M8 19l3.5-3.5M16 20V5m0 0-3.5 3.5M16 5l3.5 3.5"/></svg>
                                    </button>
                                </div>
                            @endif
                        @endforeach

                        <div class="mt-5 grid grid-cols-[1fr_1fr_auto] items-end gap-3">
                            <label>
                                <span class="text-[11px] font-bold uppercase tracking-[0.16em] text-slate-400">{{ __('time.date') }}</span>
                                <input type="date" x-model="day" class="mt-1.5 block w-full rounded-2xl border-0 bg-slate-50 px-3.5 py-3 text-sm font-semibold ring-1 ring-slate-200 focus:ring-2 focus:ring-[#C62E2E]">
                            </label>
                            <label>
                                <span class="text-[11px] font-bold uppercase tracking-[0.16em] text-slate-400">{{ __('time.time') }}</span>
                                <input type="time" x-model="time" class="mt-1.5 block w-full rounded-2xl border-0 bg-slate-50 px-3.5 py-3 text-sm font-semibold ring-1 ring-slate-200 focus:ring-2 focus:ring-[#C62E2E]">
                            </label>
                            <button type="button" @click="setNow()" class="rounded-2xl px-4 py-3 text-sm font-bold text-slate-700 ring-1 ring-slate-200 transition hover:bg-slate-900 hover:text-white">{{ __('time.now') }}</button>
                        </div>
                    </div>

                    {{-- sonuç --}}
                    <div class="flex flex-col justify-center rounded-[28px] bg-slate-950 p-6 text-white sm:p-8 lg:col-span-5">
                        <p class="text-sm text-white/60"><span x-text="label(from).split(' · ')[0]"></span> · <span class="tabular-nums" x-text="time"></span></p>
                        <p class="mt-4 text-[11px] font-bold uppercase tracking-[0.16em] text-white/45" x-text="label(to)"></p>
                        <p class="mt-1 font-mono text-7xl font-extrabold tracking-tight tabular-nums" x-text="result"></p>
                        <p class="mt-2 font-semibold capitalize text-amber-300" x-text="resultDay"></p>
                        <p class="mt-5 inline-flex w-fit rounded-full bg-white/10 px-3.5 py-1.5 text-sm font-bold tabular-nums" x-text="gapLabel"></p>
                    </div>
                </div>

                {{-- planlayıcı --}}
                <div class="mt-6 rounded-[28px] bg-white p-5 ring-1 ring-slate-200/80 sm:p-7">
                    <div class="flex flex-wrap items-end justify-between gap-3">
                        <div>
                            <h3 class="text-lg font-extrabold text-slate-900">{{ __('time.planner_title') }}</h3>
                            <p class="mt-1 text-sm text-slate-500">{{ __('time.planner_desc') }}</p>
                        </div>
                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500"><span class="size-3 rounded bg-emerald-500"></span>{{ __('time.good') }}</span>
                    </div>
                    <div class="mt-5 overflow-x-auto pb-1 [scrollbar-width:thin]">
                        <div class="grid min-w-[820px] grid-cols-[5.5rem_repeat(24,minmax(0,1fr))] gap-1 text-center text-xs font-bold tabular-nums sm:min-w-[880px] sm:grid-cols-[9rem_repeat(24,minmax(0,1fr))]">
                            <span class="sticky left-0 z-10 truncate bg-white py-2 pr-1 text-left text-slate-500 rtl:right-0 rtl:text-right" x-text="label(from).split(' · ')[0]"></span>
                            <template x-for="h in hours" :key="'a' + h.a">
                                <span class="rounded-md py-2" :class="h.good ? 'bg-emerald-500 text-white' : (h.a < 7 || h.a > 22 ? 'bg-slate-900 text-white/60' : 'bg-slate-100 text-slate-600')" x-text="window.tsTime.pad(h.a)"></span>
                            </template>
                            <span class="sticky left-0 z-10 truncate bg-white py-2 pr-1 text-left text-slate-500 rtl:right-0 rtl:text-right" x-text="label(to).split(' · ')[0]"></span>
                            <template x-for="h in hours" :key="'b' + h.a">
                                <span class="relative rounded-md py-2" :class="h.good ? 'bg-emerald-500 text-white' : (h.b < 7 || h.b > 22 ? 'bg-slate-900 text-white/60' : 'bg-slate-100 text-slate-600')">
                                    <span x-text="window.tsTime.pad(h.b) + (h.bm ? ':' + window.tsTime.pad(h.bm) : '')"></span>
                                    <span x-show="h.shift" class="absolute -top-1.5 right-0 rounded bg-amber-400 px-0.5 text-[8px] text-slate-950" x-text="h.shift > 0 ? '+1' : '−1'"></span>
                                </span>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ŞEHRİN SAAT BİLGİLERİ --}}
        <section class="bg-white py-14 md:py-20">
            <div class="mx-auto max-w-7xl px-4">
                <p class="inline-flex items-center gap-2 text-[11px] font-bold uppercase tracking-[0.2em] text-[#C62E2E]">
                    <span class="h-px w-6 bg-[#C62E2E]"></span>{{ __('time.facts_eyebrow') }}
                </p>
                <h2 class="mt-3 text-3xl font-extrabold tracking-[-0.025em] text-slate-900 sm:text-4xl">
                    {!! __('time.facts_title', ['city' => '<span class="text-[#C62E2E]">' . e($city->name) . '</span>']) !!}
                </h2>
                <dl class="mt-8 grid gap-px overflow-hidden rounded-[28px] bg-slate-200/80 ring-1 ring-slate-200/80 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ([
                        ['timezone', str_replace('_', ' ', $facts['tz'])],
                        ['utc', $facts['offset'] . ($facts['abbr'] && !preg_match('/^[+-]/', $facts['abbr']) ? ' (' . $facts['abbr'] . ')' : '')],
                        ['dst', $facts['next_change'] ? __('time.dst_next', ['date' => \Illuminate\Support\Carbon::parse($facts['next_change']['date'])->locale($locale)->isoFormat('D MMMM YYYY'), 'offset' => $facts['next_change']['offset']]) : __('time.dst_none')],
                        ['sunrise', $sun['sunrise']],
                        ['sunset', $sun['sunset']],
                        ['day_length', $dayLength],
                    ] as [$label, $value])
                        <div class="bg-white p-6">
                            <dt class="text-[11px] font-bold uppercase tracking-[0.16em] text-slate-400">{{ __('time.' . $label) }}</dt>
                            <dd class="mt-1.5 text-lg font-extrabold text-slate-900">{{ $value ?? '—' }}</dd>
                        </div>
                    @endforeach
                </dl>
            </div>
        </section>

        {{-- SİTEDEKİ ŞEHİRLERİN SAATLERİ --}}
        @if ($cities->count() > 1)
            <section class="bg-slate-50 py-14 md:py-20" x-data="{ q: '', country: '' }">
                <div class="mx-auto max-w-7xl px-4">
                    <div class="flex flex-col gap-5 md:flex-row md:items-end md:justify-between">
                        <div>
                            <p class="inline-flex items-center gap-2 text-[11px] font-bold uppercase tracking-[0.2em] text-[#C62E2E]">
                                <span class="h-px w-6 bg-[#C62E2E]"></span>{{ __('footer.time') }}
                            </p>
                            <h2 class="mt-3 text-3xl font-extrabold tracking-[-0.025em] text-slate-900 sm:text-4xl">{{ __('time.cities_title') }}</h2>
                        </div>
                        <div class="flex w-full flex-col gap-2 sm:flex-row md:w-auto">
                            @if ($countries->count() > 1)
                                <select x-model="country" aria-label="{{ __('time.all_countries') }}"
                                    class="rounded-2xl border-0 bg-white py-3 pl-4 pr-9 text-sm font-semibold text-slate-900 ring-1 ring-slate-200 focus:ring-2 focus:ring-[#C62E2E] sm:w-56">
                                    <option value="">{{ __('time.all_countries') }}</option>
                                    @foreach ($countries as $country)
                                        <option value="{{ $country->code }}">{{ $flag($country->code) }} {{ $countryName($country) }}</option>
                                    @endforeach
                                </select>
                            @endif
                            <label class="relative sm:w-64">
                                <span class="sr-only">{{ __('time.search') }}</span>
                                <svg class="pointer-events-none absolute left-4 top-1/2 size-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg>
                                <input type="search" x-model="q" placeholder="{{ __('time.search') }}" autocomplete="off"
                                    class="block w-full rounded-2xl border-0 bg-white py-3 pl-11 pr-4 text-sm ring-1 ring-slate-200 placeholder:text-slate-400 focus:ring-2 focus:ring-[#C62E2E]">
                            </label>
                        </div>
                    </div>
                    <ul class="mt-8 divide-y divide-slate-100 overflow-hidden rounded-[28px] bg-white ring-1 ring-slate-200/80">
                        @foreach ($cities as $item)
                            @php $tz = $cityZones[$item->id]; $selected = $item->is($city); @endphp
                            <li x-show="(!country || country === @js($item->country?->code)) && (!q || @js(mb_strtolower($item->name . ' ' . $countryName($item->country))).includes(q.toLowerCase()))">
                                <a href="{{ route('time', ['city' => $item->slug]) }}#top"
                                    @class(['group grid grid-cols-[minmax(0,1fr)_auto_auto] items-center gap-4 px-5 py-4 transition sm:px-6', 'bg-[#C62E2E]/[0.05]' => $selected, 'hover:bg-slate-50' => !$selected])>
                                    <span class="flex min-w-0 items-center gap-3">
                                        <span class="text-xl leading-none">{{ $flag($item->country?->code) }}</span>
                                        <span class="min-w-0">
                                            <span @class(['block truncate font-extrabold', 'text-[#C62E2E]' => $selected, 'text-slate-900 group-hover:text-[#C62E2E]' => !$selected])>{{ $item->name }}</span>
                                            <span class="block truncate text-xs text-slate-400">{{ $countryName($item->country) }} · {{ \App\Support\LocalTime::formatOffset(\Illuminate\Support\Carbon::now($tz)->getOffset()) }}</span>
                                        </span>
                                    </span>
                                    <span class="hidden text-right text-sm font-semibold text-slate-500 sm:block" x-text="diffLabel(@js($tz), mine)"></span>
                                    <span class="flex items-center gap-3">
                                        <strong class="font-mono text-2xl font-extrabold tabular-nums text-slate-900" x-text="clock(@js($tz))">{{ \Illuminate\Support\Carbon::now($tz)->format('H:i') }}</strong>
                                        <span aria-hidden="true" class="text-[#C62E2E] transition-transform group-hover:translate-x-1 rtl:rotate-180">→</span>
                                    </span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </section>
        @endif
    </div>

    {{-- yapısal veri: şehrin saati sayfası + ücretsiz saat farkı hesaplayıcı --}}
    @jsonld(\App\Support\Schema::webPage('WebPage', __('time.page_city_title', ['city' => $city->name]), __('time.page_city_description', ['city' => $city->name]), [
        'about' => array_filter([
            '@type' => 'City',
            'name' => $city->name,
            'url' => route('cities.show', ['slug' => $city->slug]),
            'geo' => $city->latitude ? ['@type' => 'GeoCoordinates', 'latitude' => (float) $city->latitude, 'longitude' => (float) $city->longitude] : null,
            'containedInPlace' => $city->country ? ['@type' => 'Country', 'name' => $city->country->name] : null,
        ]),
        'mainEntity' => [
            '@type' => 'WebApplication',
            'name' => __('time.converter_title'),
            'url' => \App\Support\Schema::canonical(),
            'applicationCategory' => 'UtilitiesApplication',
            'operatingSystem' => 'Any',
            'browserRequirements' => 'Requires JavaScript',
            'isAccessibleForFree' => true,
            'offers' => ['@type' => 'Offer', 'price' => 0, 'priceCurrency' => 'USD'],
            'provider' => \App\Support\Schema::organizationRef(),
        ],
        'primaryImageOfPage' => $banner['desktop'] ?? null,
    ]))
    @endif

    <x-page-sections page="time" />
@endsection
