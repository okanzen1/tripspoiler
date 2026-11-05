{{--
    Anasayfa hero: tam ekran arka plan görseli, üstünde koyu geçiş ve beyaz yazılar.
    Veriler HomeController::hero() — hepsi panelden:
    $banner (Yönetim > Site ayarları: görsel, telefon görseli, odak, koyulaştırma), $pass, $passCount,
    $guide, $featured, $cities, $stats.
    Header bu sayfada görselin üstünde şeffaf durur (partials/header, data-transparent).
--}}
@php
    $passUrl = $pass ? ($pass->affiliate_url ?: route('activities.show', $pass->slug)) : null;
    $overlay = [
        'soft' => 'from-slate-950/75 via-slate-950/35 to-transparent',
        'medium' => 'from-slate-950/90 via-slate-950/60 to-slate-950/10',
        'strong' => 'from-slate-950/95 via-slate-950/75 to-slate-950/30',
    ][$banner['overlay']];
@endphp

<section data-dock-start class="relative isolate -mt-[69px] md:-mt-[81px] overflow-hidden bg-slate-950 text-white">

    {{-- BACKGROUND --}}
    <picture class="absolute inset-0 -z-20 overflow-hidden">
        @if ($banner['mobile'])
            <source media="(max-width: 767px)" srcset="{{ $banner['mobile'] }}">
        @endif
        <img src="{{ $banner['src'] }}" @if ($banner['srcset']) srcset="{{ $banner['srcset'] }}" sizes="100vw" @endif
            alt="{{ __('home-hero.banner_alt') }}" fetchpriority="high" decoding="async"
            style="object-position: {{ $banner['focus'] }}% center"
            @class(['hero-zoom w-full h-full object-cover', 'max-md:![object-position:50%_center]' => $banner['mobile']])>
    </picture>
    <div class="absolute inset-0 -z-10 bg-gradient-to-r rtl:bg-gradient-to-l {{ $overlay }}"></div>
    <div class="absolute inset-x-0 top-0 -z-10 h-40 bg-gradient-to-b from-slate-950/70 to-transparent"></div>
    <div class="absolute inset-x-0 bottom-0 -z-10 h-1/2 bg-gradient-to-t from-slate-950/95 via-slate-950/60 to-transparent"></div>

    {{-- Masaüstünde hero ekranı tam doldurur (açıksa üstteki duyuru şeridi düşülür, --bar-h), içerik dikeyde ortalanır --}}
    <div class="max-w-7xl mx-auto px-4 pt-[124px] md:pt-[calc(80px+clamp(8px,3.2vh,64px))] pb-10 md:pb-[clamp(14px,3.5vh,48px)]
                lg:min-h-[calc(100svh-var(--bar-h,0px))] lg:flex lg:flex-col lg:justify-center">

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">

            <div class="lg:col-span-7 xl:col-span-7">

                {{-- EYEBROW --}}
                <p style="--d: 0ms" class="hero-reveal flex flex-wrap items-center gap-x-2 gap-y-1 text-[11px] md:text-xs font-bold uppercase tracking-[0.2em] text-white/75">
                    <span class="inline-block w-6 h-px bg-[#E0393E]"></span>
                    {{ $texts['eyebrow'] }}
                </p>

                {{-- HEADLINE --}}
                <h1 style="--d: 90ms" class="hero-reveal mt-5 lg:mt-[clamp(8px,1.8vh,24px)] text-[44px] leading-[0.98] sm:text-6xl lg:text-[clamp(42px,6.8vh,80px)] font-extrabold tracking-[-0.035em] drop-shadow-[0_2px_24px_rgba(0,0,0,0.35)]">
                    <span class="block">{{ $texts['title_1'] }}</span>
                    <span class="block">{{ $texts['title_2'] }}</span>
                    <span class="block">
                        {{ $texts['title_3'] }}
                        <span class="text-[#E0393E]">{{ $texts['title_highlight'] }}</span>
                    </span>
                </h1>

                {{-- SUBTEXT --}}
                <p style="--d: 180ms" class="hero-reveal mt-6 lg:mt-[clamp(10px,2.4vh,28px)] max-w-2xl text-base md:text-lg xl:text-xl leading-relaxed text-white/85">
                    {{ $texts['description'] }}
                </p>

                {{-- SEARCH --}}
                <div style="--d: 270ms" class="hero-reveal mt-8 lg:mt-[clamp(12px,3vh,36px)] lg:max-w-xl">
                    <x-search-modal :hero="true" />
                </div>
            </div>

            {{-- RIGHT: DÖNEN "ŞU AN POPÜLER" KARTI --}}
            @if ($featured->isNotEmpty())
                <div class="hidden lg:block lg:col-span-5 xl:col-span-5">
                    <div x-data="{
                            i: 0,
                            n: {{ $featured->count() }},
                            timer: null,
                            start() { this.stop(); this.timer = setInterval(() => this.i = (this.i + 1) % this.n, 4500) },
                            stop() { clearInterval(this.timer) },
                            go(step) { this.i = (this.i + step + this.n) % this.n },
                            touchX: null,
                            swipe(endX) { if (this.touchX !== null && Math.abs(endX - this.touchX) > 40) { this.go(endX < this.touchX ? 1 : -1); this.start() } this.touchX = null },
                        }" x-init="start()" @mouseenter="stop()" @mouseleave="start()"
                        @touchstart.passive="touchX = $event.touches[0].clientX; stop()" @touchend="swipe($event.changedTouches[0].clientX)"
                        style="--d: 420ms"
                        class="hero-reveal hero-float relative w-full lg:ml-auto lg:max-w-[clamp(280px,44vh,420px)] rounded-[28px] border border-white/20 bg-white/10 p-3
                               backdrop-blur-xl shadow-[0_30px_80px_-20px_rgba(0,0,0,0.6)]">

                        {{-- Kapaklar 3:2; oranı farklı olan kesilmez, kenarları aynı görselin bulanık hâliyle dolar --}}
                        <div class="group/slides relative aspect-[3/2] overflow-hidden rounded-[20px] bg-slate-800">
                            @foreach ($featured as $activity)
                                <div :class="i === {{ $loop->index }} ? 'opacity-100' : 'opacity-0'"
                                    @class(['absolute inset-0 transition-opacity duration-700 ease-out', 'opacity-0' => !$loop->first])>
                                    <img src="{{ $activity->cover->url('sm') }}" alt="" aria-hidden="true" loading="lazy" class="absolute inset-0 h-full w-full scale-110 object-cover blur-xl opacity-60">
                                    <img src="{{ $activity->cover->url() }}" alt="{{ $activity->name }}" @if (!$loop->first) loading="lazy" @endif
                                        :class="i === {{ $loop->index }} ? 'scale-100' : 'scale-105'"
                                        class="relative h-full w-full object-contain transition-transform duration-[1200ms] ease-[cubic-bezier(0.22,1,0.36,1)]">
                                </div>
                            @endforeach
                            <div class="pointer-events-none absolute inset-x-0 bottom-0 h-1/3 bg-gradient-to-t from-black/50 to-transparent"></div>

                            {{-- ÖNCEKİ / SONRAKİ --}}
                            @if ($featured->count() > 1)
                                @foreach (['prev' => ['left-2', 'M15 18l-6-6 6-6', -1], 'next' => ['right-2', 'M9 6l6 6-6 6', 1]] as $dir => [$side, $path, $step])
                                    <button type="button" @click="go({{ $step }})" aria-label="{{ $dir }}"
                                        class="absolute {{ $side }} top-1/2 -translate-y-1/2 grid size-9 place-items-center rounded-full bg-white/85 text-slate-900 shadow-lg
                                               opacity-100 transition duration-300 hover:bg-white lg:opacity-0 lg:group-hover/slides:opacity-100 focus-visible:opacity-100">
                                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $path }}" /></svg>
                                    </button>
                                @endforeach
                            @endif

                            <span class="absolute left-3 top-3 inline-flex items-center gap-1.5 rounded-full bg-white/90 px-3 py-1 text-[11px] font-bold uppercase tracking-wider text-slate-900">
                                <span class="relative flex size-2">
                                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-[#E0393E] opacity-75"></span>
                                    <span class="relative inline-flex size-2 rounded-full bg-[#E0393E]"></span>
                                </span>
                                {{ __('home-benefits.phone_popular') }}
                            </span>

                            {{-- DOTS --}}
                            <div class="absolute bottom-2 left-1/2 -translate-x-1/2 flex items-center gap-0.5">
                                @foreach ($featured as $activity)
                                    <button type="button" @click="i = {{ $loop->index }}" aria-label="{{ $loop->iteration }}"
                                        class="group/dot grid h-5 place-items-center px-0.5">
                                        <span :class="i === {{ $loop->index }} ? 'w-6 bg-white' : 'w-1.5 bg-white/55 group-hover/dot:bg-white/90'"
                                            class="block h-1.5 rounded-full transition-all duration-500"></span>
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        {{-- Tüm kayıtlar aynı hücrede üst üste; sadece seçili olan görünür (geçişte binişme olmaz) --}}
                        <div class="grid px-2 pt-3.5 pb-1.5">
                            @foreach ($featured as $activity)
                                <div :class="i === {{ $loop->index }} ? 'opacity-100 translate-y-0 pointer-events-auto' : 'opacity-0 translate-y-2 pointer-events-none'"
                                    :aria-hidden="i !== {{ $loop->index }}"
                                    @class(['[grid-area:1/1] transition duration-500 ease-out',
                                            'opacity-0 translate-y-2 pointer-events-none' => !$loop->first])>
                                    <p class="text-xs font-medium text-white/65">
                                        {{ $activity->city?->name }}@if ($activity->audio_guide) · {{ __('home-benefits.phone_audio') }}@endif
                                    </p>
                                    <p class="mt-1 min-h-[2.75rem] text-[15px] font-bold leading-snug text-white line-clamp-2">{{ $activity->name }}</p>
                                    <a href="{{ $activity->affiliate_url ?: route('activities.show', $activity->slug) }}" target="_blank" rel="noopener noreferrer"
                                        @if (!$loop->first) tabindex="-1" @endif :tabindex="i === {{ $loop->index }} ? 0 : -1"
                                        class="group mt-2.5 inline-flex items-center gap-1.5 text-sm font-semibold text-white">
                                        <span class="border-b border-white/40 pb-0.5 transition group-hover:border-white">{{ rtrim(__('most-popular-activities.cta'), ' →') }}</span>
                                        <span aria-hidden="true" class="transition-transform duration-300 group-hover:translate-x-1">→</span>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

        </div>

        {{-- TELEFON / TABLET: "Şu an popüler" yana kayan küçük kartlar (masaüstünde sağdaki dönen kart var) --}}
        @if ($featured->isNotEmpty())
            <div style="--d: 400ms" class="hero-reveal mt-8 lg:hidden">
                <div class="flex items-center justify-between">
                    <p class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-[0.16em] text-white">
                        <span class="relative flex size-2">
                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-[#E0393E] opacity-75"></span>
                            <span class="relative inline-flex size-2 rounded-full bg-[#E0393E]"></span>
                        </span>
                        {{ __('home-benefits.phone_popular') }}
                    </p>
                    <a href="{{ route('activities.index') }}" class="text-sm font-semibold text-white/80 hover:text-white">{{ __('home-benefits.phone_see_all') }} →</a>
                </div>
                <div class="-mx-4 mt-3 flex snap-x snap-mandatory scroll-px-4 gap-3 overflow-x-auto px-4 pb-1 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                    @foreach ($featured as $activity)
                        <a href="{{ $activity->affiliate_url ?: route('activities.show', $activity->slug) }}" target="_blank" rel="noopener noreferrer"
                            class="group w-[64%] sm:w-[38%] shrink-0 snap-start overflow-hidden rounded-2xl bg-white/10 ring-1 ring-white/15 backdrop-blur-md transition active:scale-[0.98]">
                            <span class="block aspect-[3/2] overflow-hidden bg-slate-800">
                                <img src="{{ $activity->cover->url('sm') }}" alt="{{ $activity->name }}" loading="lazy" decoding="async"
                                    class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                            </span>
                            <span class="block p-3">
                                <span class="block text-[11px] font-medium text-white/65">{{ $activity->city?->name }}@if ($activity->audio_guide) · {{ __('home-benefits.phone_audio') }}@endif</span>
                                <span class="mt-0.5 block text-sm font-semibold leading-snug text-white line-clamp-2">{{ $activity->name }}</span>
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- TICKET CARDS --}}
        @if ($pass || $guide)
            <div style="--d: 480ms" class="hero-reveal mt-8 lg:mt-[clamp(14px,4.2vh,56px)] -mx-4 flex snap-x snap-mandatory scroll-px-4 gap-3 overflow-x-auto px-4 pb-2 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden lg:mx-0 lg:grid lg:grid-cols-2 lg:gap-5 lg:overflow-visible lg:px-0 lg:pb-0">
                {{-- Telefon/tablette kartlar yan yana kayar (sonrakinin kenarı görünür), lg ve üstünde iki sütun --}}

                {{-- CITY PASS --}}
                @if ($pass)
                    <div class="relative flex w-[88%] sm:w-[62%] shrink-0 snap-center flex-row overflow-hidden rounded-2xl bg-white text-slate-900 lg:w-auto
                                shadow-[0_24px_60px_-20px_rgba(0,0,0,0.6)] transition duration-500 ease-[cubic-bezier(0.22,1,0.36,1)]
                                hover:-translate-y-1.5 hover:shadow-[0_36px_80px_-20px_rgba(0,0,0,0.7)]">

                        <div class="min-w-0 flex-1 p-4 sm:p-5">
                            <div class="flex items-center gap-3">
                                <span class="inline-flex items-center gap-1 whitespace-nowrap rounded-md bg-[#C62E2E] px-2 py-1 text-[11px] font-bold uppercase tracking-wider text-white">
                                    ★ {{ __('home-hero.pass_badge') }}
                                </span>
                                <span class="hidden sm:inline text-[11px] font-bold uppercase tracking-wider text-[#C62E2E]">
                                    {{ __('home-hero.pass_tag') }}
                                </span>
                            </div>
                            <h2 class="mt-3 text-[15px] sm:text-lg md:text-xl font-bold leading-snug line-clamp-3 sm:line-clamp-2">
                                {{ $pass->name }}
                            </h2>
                            <p class="mt-2 text-sm text-slate-500">
                                {{ __('home-hero.pass_meta', ['count' => $passCount, 'city' => $pass->city?->name]) }}
                            </p>
                        </div>

                        <div class="relative w-[38%] sm:w-48 shrink-0 p-4 sm:p-5 flex flex-col justify-center
                                    border-l border-dashed border-slate-300">
                            <span class="absolute -left-[9px] -top-[9px] size-[18px] rounded-full bg-slate-950"></span>
                            <span class="absolute -left-[9px] -bottom-[9px] size-[18px] rounded-full bg-slate-950"></span>

                            <p class="text-xs font-medium text-slate-500">{{ __('home-hero.pass_compare') }}</p>
                            <p class="text-2xl sm:text-3xl font-extrabold tracking-tight">{{ $passCount }}</p>
                            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">{{ __('home-hero.pass_unit') }}</p>
                            <a href="{{ $passUrl }}" target="_blank" rel="noopener noreferrer"
                                class="mt-3 inline-flex items-center justify-center rounded-xl bg-[#C62E2E] px-2 sm:px-5 py-2.5 text-xs sm:text-sm
                                       font-semibold text-white hover:bg-[#A82424] transition duration-300 hover:shadow-lg active:scale-[0.98]">
                                {{ __('home-hero.pass_cta') }}
                            </a>
                        </div>
                    </div>
                @endif

                {{-- LATEST GUIDE --}}
                @if ($guide)
                    <div class="relative flex w-[88%] sm:w-[62%] shrink-0 snap-center flex-row overflow-hidden rounded-2xl bg-white text-slate-900 lg:w-auto
                                shadow-[0_24px_60px_-20px_rgba(0,0,0,0.6)] transition duration-500 ease-[cubic-bezier(0.22,1,0.36,1)]
                                hover:-translate-y-1.5 hover:shadow-[0_36px_80px_-20px_rgba(0,0,0,0.7)]">

                        <div class="min-w-0 flex-1 p-4 sm:p-5">
                            <div class="flex items-center gap-3">
                                <span class="rounded-md bg-slate-900 px-2 py-1 text-[11px] font-bold uppercase tracking-wider text-white">
                                    {{ __('home-hero.guide_badge') }}
                                </span>
                                <span class="hidden sm:inline text-[11px] font-bold uppercase tracking-wider text-slate-900">
                                    {{ __('home-hero.guide_tag') }}
                                </span>
                            </div>
                            <h2 class="mt-3 text-[15px] sm:text-lg md:text-xl font-bold leading-snug line-clamp-3 sm:line-clamp-2">
                                {{ $guide->title }}
                            </h2>
                            <p class="mt-2 text-sm text-slate-500 line-clamp-2">
                                {{ $guide->excerpt }}
                            </p>
                        </div>

                        <div class="relative w-[38%] sm:w-48 shrink-0 p-4 sm:p-5 flex flex-col justify-center
                                    border-l border-dashed border-slate-300">
                            <span class="absolute -left-[9px] -top-[9px] size-[18px] rounded-full bg-slate-950"></span>
                            <span class="absolute -left-[9px] -bottom-[9px] size-[18px] rounded-full bg-slate-950"></span>

                            <p class="text-xs font-medium text-slate-500">{{ __('home-hero.guide_reading') }}</p>
                            <p class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                                {{ __('home-hero.guide_minutes', ['count' => $guide->readingMinutes()]) }}
                            </p>
                            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">{{ __('home-hero.guide_unit') }}</p>
                            <a href="{{ route('blog.show', $guide->slug) }}"
                                class="mt-3 inline-flex items-center justify-center rounded-xl bg-slate-900 px-2 sm:px-5 py-2.5 text-xs sm:text-sm
                                       font-semibold text-white hover:bg-slate-700 transition duration-300 hover:shadow-lg active:scale-[0.98]">
                                {{ __('home-hero.guide_cta') }}
                            </a>
                        </div>
                    </div>
                @endif

            </div>
        @endif

        {{-- STATS --}}
        <dl style="--d: 560ms" class="hero-reveal mt-8 lg:mt-[clamp(10px,2.6vh,40px)] grid grid-cols-4 gap-x-2 divide-x divide-white/20 md:divide-white/25">
            @foreach ([
                ['value' => $stats['experiences'], 'label' => __('home-hero.stat_experiences'),
                 'icon' => '<path d="M4 8a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v1.5a2.5 2.5 0 0 0 0 5V16a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-1.5a2.5 2.5 0 0 0 0-5z"/><path d="M14 6v12" stroke-dasharray="2 2"/>'],
                ['value' => $cities->count(), 'label' => __('home-hero.stat_cities'),
                 'icon' => '<path d="M12 21s-7-6.2-7-11.5A7 7 0 0 1 19 9.5C19 14.8 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/>'],
                ['value' => $stats['reviews'] > 0 ? number_format($stats['rating'], 1) : '—', 'label' => trans_choice('home-hero.stat_reviews', $stats['reviews'], ['count' => $stats['reviews']]),
                 'icon' => '<path d="M12 3l2.7 5.6 6.1.9-4.4 4.3 1 6.1L12 17l-5.4 2.9 1-6.1-4.4-4.3 6.1-.9z"/>'],
                ['value' => $stats['languages'], 'label' => __('home-hero.stat_languages'),
                 'icon' => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c2.5 2.6 3.8 5.6 3.8 9s-1.3 6.4-3.8 9c-2.5-2.6-3.8-5.6-3.8-9S9.5 5.6 12 3z"/>'],
            ] as $stat)
                <div class="flex flex-col items-center gap-1 px-1 text-center md:flex-row md:gap-3.5 md:px-8 md:text-left md:first:pl-0">
                    <svg class="hidden md:block w-8 h-8 shrink-0 text-[#E0393E]" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">{!! $stat['icon'] !!}</svg>
                    <div class="flex flex-col-reverse">
                        <dt class="text-[11px] leading-tight md:text-sm font-medium text-white/80">{{ $stat['label'] }}</dt>
                        <dd class="text-xl md:text-2xl font-extrabold tracking-tight text-white drop-shadow-[0_1px_8px_rgba(0,0,0,0.5)]">{{ $stat['value'] }}</dd>
                    </div>
                </div>
            @endforeach
        </dl>

    </div>
</section>
