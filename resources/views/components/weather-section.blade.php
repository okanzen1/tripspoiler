{{--
    Hava durumu bölümü (panel: Sayfalar › sayfa › Bölümler › Hava durumu): seçili şehrin şu anki havası, 7 günlük tahmin,
    gün doğumu/batımı, "ne giysem" ve havaya göre aktiviteler (aktivitede "Mekan": kapalı / açık hava).
    Tasarım: sitenin lacivert paneli (döviz / fırsatlar sayfalarıyla aynı), kırmızı + altın vurgu.
    Veri App\Support\Weather (MET Norway, saatte bir). Veri yoksa bölüm hiç görünmez.
--}}
@props(['cityId' => null, 'color' => 'bg-white'])

@php
    $city = $cityId ? \App\Models\City::with('country')->find($cityId) : null;
    $forecast = null;
    try {
        $forecast = $city ? \App\Support\Weather::forecast($city) : null;
    } catch (\Throwable $e) {
        report($e);
    }
    if ($forecast) {
        $locale = app()->getLocale();
        $tz = $forecast['tz'];
        $sun = \App\Support\Weather::sun($city);
        $tips = \App\Support\Weather::tips($forecast['days']);
        $wet = \App\Support\Weather::isWet($forecast['days']);
        $today = \Illuminate\Support\Carbon::now($tz)->toDateString();
        $days = collect($forecast['days'])->filter(fn ($d) => $d['date'] >= $today)->take(7)->values();
        $todayData = $days->first();
        try {
            $suggestions = \App\Models\Activity::visible()->with('cover')->where('city_id', $city->id)
                ->whereIn('setting', $wet ? ['indoor', 'both'] : ['outdoor', 'both'])
                ->orderByDesc('is_popular')->orderBy('sort_order')->take(4)->get();
        } catch (\Throwable) {
            $suggestions = collect(); // panelde migrate henüz çalışmadıysa (setting yok)
        }
        $dayName = fn (string $date) => $date === $today ? __('weather.today') : \Illuminate\Support\Carbon::parse($date, $tz)->locale($locale)->isoFormat('dddd');
    }
@endphp

@if ($forecast && $todayData)
    <section id="weather" class="{{ $color }} scroll-mt-28 py-16 md:py-24">
        <div class="mx-auto max-w-7xl px-4">
            {{-- başlık (sitenin diğer bölümleriyle aynı) --}}
            <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                <div class="max-w-2xl">
                    <p class="inline-flex items-center gap-2 text-[11px] font-bold uppercase tracking-[0.2em] text-[#C62E2E]">
                        <span class="h-px w-6 bg-[#C62E2E]"></span>{{ __('weather.eyebrow') }}
                    </p>
                    <h2 class="mt-3 text-3xl font-extrabold leading-[1.08] tracking-[-0.025em] text-slate-900 sm:text-4xl lg:text-[44px]">
                        {!! __('weather.title', ['city' => '<span class="text-[#C62E2E]">' . e($city->name) . '</span>']) !!}
                    </h2>
                </div>
                <a href="{{ route('weather', ['city' => $city->slug]) }}" class="group inline-flex items-center gap-1.5 text-sm font-semibold text-slate-900">
                    <span class="border-b border-slate-300 pb-0.5 transition group-hover:border-slate-900">{{ __('weather.details') }}</span>
                    <span aria-hidden="true" class="transition-transform group-hover:translate-x-1 rtl:rotate-180">→</span>
                </a>
            </div>

            {{-- PANEL --}}
            <div class="relative isolate mt-8 overflow-hidden rounded-[32px] bg-slate-950 text-white shadow-[0_40px_80px_-40px_rgba(15,23,42,0.8)]">
                <div class="pointer-events-none absolute inset-0 -z-10" aria-hidden="true">
                    <div class="absolute inset-0 opacity-[0.06] [background-image:radial-gradient(rgba(255,255,255,.9)_1px,transparent_1px)] [background-size:24px_24px]"></div>
                    <div class="absolute -left-32 -top-32 size-[28rem] rounded-full bg-[radial-gradient(circle,rgba(198,46,46,0.39),transparent_65%)]"></div>
                    <div class="absolute -bottom-40 right-0 size-[26rem] rounded-full bg-[radial-gradient(circle,rgba(251,191,36,0.20),transparent_65%)]"></div>
                </div>

                <div class="grid lg:grid-cols-12">
                    {{-- şu an --}}
                    <div class="border-b border-white/10 p-6 sm:p-8 lg:col-span-5 lg:border-b-0 lg:border-r rtl:lg:border-l rtl:lg:border-r-0">
                        <p class="text-sm font-medium text-white/55">{{ $city->name }} · {{ __('weather.now') }} {{ \Illuminate\Support\Carbon::now($tz)->format('H:i') }}</p>
                        <div class="mt-4 flex items-center gap-5">
                            <x-weather-icon :condition="$forecast['now']['condition']" class="size-24 drop-shadow-[0_10px_30px_rgba(251,191,36,0.25)]" />
                            <div>
                                <p class="text-7xl font-extrabold leading-none tracking-tighter tabular-nums">{{ $forecast['now']['temp'] }}°</p>
                                <p class="mt-2 text-lg font-bold text-amber-300">{{ __('weather.c_' . $forecast['now']['condition']) }}</p>
                            </div>
                        </div>
                        <p class="mt-4 text-sm text-white/60 tabular-nums">
                            {{ __('weather.high') }} <strong class="font-bold text-white">{{ $todayData['max'] }}°</strong>
                            <span class="px-1.5 text-white/25">/</span>
                            {{ __('weather.low') }} <strong class="font-bold text-white">{{ $todayData['min'] }}°</strong>
                        </p>

                        <dl class="mt-7 grid grid-cols-2 gap-px overflow-hidden rounded-2xl bg-white/10 text-sm ring-1 ring-white/10">
                            @foreach ([
                                ['wind', $forecast['now']['wind'] . ' km/h'],
                                ['humidity', '%' . $forecast['now']['humidity']],
                                ['sunrise', $sun['sunrise']],
                                ['sunset', $sun['sunset']],
                            ] as [$label, $value])
                                <div class="bg-slate-950/80 px-4 py-3">
                                    <dt class="text-[11px] font-semibold uppercase tracking-[0.14em] text-white/45">{{ __('weather.' . $label) }}</dt>
                                    <dd class="mt-0.5 font-bold tabular-nums">{{ $value ?? '—' }}</dd>
                                </div>
                            @endforeach
                        </dl>
                        @if ($sun['golden'])
                            <p class="mt-4 text-xs text-white/50">{{ __('weather.golden_hour', ['time' => $sun['golden']]) }}</p>
                        @endif
                    </div>

                    {{-- 7 gün --}}
                    <div class="p-3 sm:p-5 lg:col-span-7">
                        <p class="px-3 pb-2 pt-2 text-[11px] font-bold uppercase tracking-[0.18em] text-white/45">{{ __('weather.forecast') }}</p>
                        <ul class="divide-y divide-white/[0.07]">
                            @foreach ($days as $day)
                                <li @class(['grid grid-cols-[minmax(0,1fr)_auto_auto] items-center gap-4 rounded-2xl px-3 py-2.5 sm:grid-cols-[8rem_minmax(0,1fr)_auto_auto]', 'bg-white/[0.06]' => $loop->first])>
                                    <span @class(['truncate text-sm font-bold capitalize', 'text-amber-300' => $loop->first, 'text-white/85' => !$loop->first])>{{ $dayName($day['date']) }}</span>
                                    <span class="hidden min-w-0 items-center gap-2 sm:flex">
                                        <span class="truncate text-sm text-white/55">{{ __('weather.c_' . $day['condition']) }}</span>
                                        @if ($day['rain'] >= 0.5)
                                            <span class="shrink-0 rounded-full bg-white/10 px-2 py-0.5 text-[11px] font-semibold tabular-nums text-white/70">{{ rtrim(rtrim(number_format($day['rain'], 1, ',', ''), '0'), ',') }} mm</span>
                                        @endif
                                    </span>
                                    <x-weather-icon :condition="$day['condition']" class="size-8" />
                                    <span class="w-20 text-right text-sm tabular-nums rtl:text-left">
                                        <strong class="font-extrabold text-white">{{ $day['max'] }}°</strong>
                                        <span class="ml-1.5 text-white/40">{{ $day['min'] }}°</span>
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                {{-- ne giysem --}}
                @if ($tips)
                    <div class="flex flex-col gap-3 border-t border-white/10 px-6 py-5 sm:flex-row sm:items-center sm:gap-6 sm:px-8">
                        <p class="shrink-0 text-[11px] font-bold uppercase tracking-[0.18em] text-amber-300">{{ __('weather.tips_title') }}</p>
                        <ul class="flex flex-col gap-1.5 text-sm text-white/80 sm:flex-row sm:flex-wrap sm:gap-x-6">
                            @foreach ($tips as $tip)
                                <li class="flex items-start gap-2"><span class="mt-1.5 size-1.5 shrink-0 rounded-full bg-[#FF6B6E]"></span>{{ __('weather.' . $tip) }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>

            {{-- bu havada ne yapılır --}}
            @if ($suggestions->isNotEmpty())
                <div class="mt-10">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">{{ __($wet ? 'weather.suggest_wet' : 'weather.suggest_dry') }}</p>
                    <ul class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        @foreach ($suggestions as $activity)
                            <li>
                                <a href="{{ route('activities.show', ['slug' => $activity->slug]) }}" class="group flex h-full items-center gap-3 rounded-2xl bg-white p-3 ring-1 ring-slate-200 transition hover:-translate-y-0.5 hover:shadow-lg hover:ring-[#C62E2E]/30">
                                    @if ($cover = $activity->cover?->url('sm'))
                                        <img src="{{ $cover }}" alt="" loading="lazy" class="size-16 shrink-0 rounded-xl object-cover">
                                    @endif
                                    <span class="line-clamp-2 text-sm font-bold leading-snug text-slate-900 group-hover:text-[#C62E2E]">{{ $activity->name }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <p class="mt-5 text-xs text-slate-400">
                {{ __('weather.updated', ['time' => \Illuminate\Support\Carbon::createFromTimestamp($forecast['updated'], $tz)->format('H:i')]) }}
                · <a href="https://www.met.no/en" target="_blank" rel="noopener nofollow" class="hover:text-slate-600 hover:underline">{{ __('weather.source') }}: MET Norway</a>
            </p>
        </div>
    </section>
@endif
