{{--
    Yerel saat bölümü (panel: Sayfalar › sayfa › Bölümler › Yerel saat): seçili şehrin canlı işleyen saati, ziyaretçiyle saat farkı
    (tarayıcının saat diliminden), UTC farkı / yaz saati, gün doğumu/batımı, gece-gündüz ve aramak için uygun saatler.
    Tasarım: hava durumu bölümüyle aynı lacivert panel. Dış servis yok (resources/js/local-time.js).
--}}
@props(['cityId' => null, 'color' => 'bg-white'])

@php
    $city = $cityId ? \App\Models\City::with('country')->find($cityId) : null;
    if ($city) {
        $locale = app()->getLocale();
        $facts = \App\Support\LocalTime::facts($city);
        $sun = \App\Support\Weather::sun($city);
        $now = \Illuminate\Support\Carbon::now($facts['tz']);
    }
@endphp

@if ($city)
    <section id="local-time" class="{{ $color }} scroll-mt-28 py-16 md:py-24"
        x-data="{
            tz: @js($facts['tz']),
            locale: @js($locale),
            mine: '',
            now: new Date(),
            timer: null,
            init() {
                this.mine = window.tsTime.visitorZone();
                this.timer = setInterval(() => this.now = new Date(), 1000);
            },
            destroy() { clearInterval(this.timer) },
            get p() { return window.tsTime.parts(this.tz, this.now) },
            get clock() { const p = this.p; return window.tsTime.pad(p.h) + ':' + window.tsTime.pad(p.mi) },
            get seconds() { return window.tsTime.pad(this.p.s) },
            get date() { return window.tsTime.format(this.now, this.tz, this.locale, { weekday: 'long', day: 'numeric', month: 'long' }) },
            get myClock() { return window.tsTime.format(this.now, this.mine, this.locale, { hour: '2-digit', minute: '2-digit', hourCycle: 'h23' }) },
            get diff() { return window.tsTime.offset(this.tz, this.now) - window.tsTime.offset(this.mine, this.now) },
            get diffLabel() {
                if (this.diff === 0) return @js(__('time.same'));
                const text = window.tsTime.diffText(this.diff, { h: @js(__('time.h')), m: @js(__('time.m')) });
                return (this.diff > 0 ? @js(__('time.ahead')) : @js(__('time.behind'))).replace(':diff', text);
            },
            // aramak için uygun saat: şehrin 09:00–21:00'i ziyaretçinin saatinde
            get callWindow() {
                const p = this.p;
                const from = window.tsTime.zoned(this.tz, p.y, p.mo, p.d, 9, 0), to = window.tsTime.zoned(this.tz, p.y, p.mo, p.d, 21, 0);
                const f = (d) => window.tsTime.format(d, this.mine, this.locale, { hour: '2-digit', minute: '2-digit', hourCycle: 'h23' });
                return f(from) + '–' + f(to);
            },
            get isDay() { const m = this.p.h * 60 + this.p.mi; return m >= @js($sun['sunrise'] ? ((int) substr($sun['sunrise'], 0, 2)) * 60 + (int) substr($sun['sunrise'], 3, 2) : 360) && m < @js($sun['sunset'] ? ((int) substr($sun['sunset'], 0, 2)) * 60 + (int) substr($sun['sunset'], 3, 2) : 1080) },
        }">
        <div class="mx-auto max-w-7xl px-4">
            <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                <div class="max-w-2xl">
                    <p class="inline-flex items-center gap-2 text-[11px] font-bold uppercase tracking-[0.2em] text-[#C62E2E]">
                        <span class="h-px w-6 bg-[#C62E2E]"></span>{{ __('time.eyebrow') }}
                    </p>
                    <h2 class="mt-3 text-3xl font-extrabold leading-[1.08] tracking-[-0.025em] text-slate-900 sm:text-4xl lg:text-[44px]">
                        {!! __('time.title', ['city' => '<span class="text-[#C62E2E]">' . e($city->name) . '</span>']) !!}
                    </h2>
                </div>
                <a href="{{ route('time', ['city' => $city->slug]) }}" class="group inline-flex items-center gap-1.5 text-sm font-semibold text-slate-900">
                    <span class="border-b border-slate-300 pb-0.5 transition group-hover:border-slate-900">{{ __('time.details') }}</span>
                    <span aria-hidden="true" class="transition-transform group-hover:translate-x-1 rtl:rotate-180">→</span>
                </a>
            </div>

            <div class="relative isolate mt-8 overflow-hidden rounded-[32px] bg-slate-950 text-white shadow-[0_40px_80px_-40px_rgba(15,23,42,0.8)]">
                <div class="pointer-events-none absolute inset-0 -z-10" aria-hidden="true">
                    <div class="absolute inset-0 opacity-[0.06] [background-image:radial-gradient(rgba(255,255,255,.9)_1px,transparent_1px)] [background-size:24px_24px]"></div>
                    <div class="absolute -right-32 -top-32 size-[28rem] rounded-full bg-[#C62E2E]/30 blur-[120px]"></div>
                    <div class="absolute -bottom-40 left-0 size-[26rem] rounded-full bg-amber-400/15 blur-[120px]"></div>
                </div>

                <div class="grid lg:grid-cols-12">
                    {{-- canlı saat --}}
                    <div class="border-b border-white/10 p-6 sm:p-8 lg:col-span-7 lg:border-b-0 lg:border-r rtl:lg:border-l rtl:lg:border-r-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-white/10 px-3 py-1 text-xs font-bold text-white/80">
                                <span class="relative flex size-2"><span class="absolute inline-flex size-full animate-ping rounded-full bg-emerald-400 opacity-60"></span><span class="relative inline-flex size-2 rounded-full bg-emerald-400"></span></span>
                                {{ __('time.live') }}
                            </span>
                            <span class="rounded-full px-3 py-1 text-xs font-bold" :class="isDay ? 'bg-amber-400 text-slate-950' : 'bg-indigo-500/30 text-indigo-100'"
                                x-text="isDay ? @js('☀ ' . __('time.day')) : @js('☾ ' . __('time.night'))">{{ __('time.day') }}</span>
                        </div>
                        <p class="mt-5 font-extrabold leading-none tracking-tighter tabular-nums" aria-live="off">
                            <span class="text-7xl sm:text-8xl lg:text-[112px]" x-text="clock">{{ $now->format('H:i') }}</span><span class="ml-1 text-3xl text-white/40 sm:text-4xl" x-text="seconds">{{ $now->format('s') }}</span>
                        </p>
                        <p class="mt-3 text-lg font-semibold capitalize text-amber-300" x-text="date">{{ $now->locale($locale)->isoFormat('dddd, D MMMM') }}</p>
                        <p class="mt-1 text-sm text-white/50">{{ str_replace('_', ' ', $facts['tz']) }} · {{ $facts['offset'] }}</p>
                    </div>

                    {{-- fark ve bilgiler --}}
                    <div class="flex flex-col p-6 sm:p-8 lg:col-span-5">
                        <div class="rounded-2xl bg-white/[0.07] p-5 ring-1 ring-white/10">
                            <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-white/45">{{ __('time.your_time') }}</p>
                            <div class="mt-1 flex items-baseline justify-between gap-3">
                                <span class="text-3xl font-extrabold tabular-nums" x-text="myClock"></span>
                                <span class="text-right text-sm font-bold text-amber-300" x-text="diffLabel"></span>
                            </div>
                        </div>
                        <dl class="mt-4 grid grid-cols-2 gap-px overflow-hidden rounded-2xl bg-white/10 text-sm ring-1 ring-white/10">
                            @foreach ([
                                ['sunrise', $sun['sunrise']],
                                ['sunset', $sun['sunset']],
                                ['utc', $facts['offset'] . ($facts['abbr'] && !str_starts_with($facts['abbr'], '+') && !str_starts_with($facts['abbr'], '-') ? ' · ' . $facts['abbr'] : '')],
                                ['dst', $facts['next_change'] ? \Illuminate\Support\Carbon::parse($facts['next_change']['date'])->locale($locale)->isoFormat('D MMM') . ' → ' . $facts['next_change']['offset'] : __('time.dst_none')],
                            ] as [$label, $value])
                                <div class="bg-slate-950/80 px-4 py-3">
                                    <dt class="text-[11px] font-semibold uppercase tracking-[0.14em] text-white/45">{{ __('time.' . $label) }}</dt>
                                    <dd class="mt-0.5 font-bold tabular-nums">{{ $value ?? '—' }}</dd>
                                </div>
                            @endforeach
                        </dl>
                        <p class="mt-4 text-sm leading-relaxed text-white/65">
                            <span class="font-bold text-white">{{ __('time.call_title') }}:</span>
                            <span x-text="@js(__('time.call_text', ['city' => $city->name])).replace(':range', callWindow)"></span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endif
