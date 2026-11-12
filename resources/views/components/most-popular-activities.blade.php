@props([
    'color' => 'bg-slate-50',
    // Başlık değiştirilebilir (ör. aktivite detayında "Istanbul için başka öneriler")
    'heading' => [],
])

{{--
    Popüler aktiviteler: yatay kayan kartlar (tarayıcının kendi kaydırması, telefonda parmakla), masaüstünde oklar.
    Kapaklar çoğu zaman yazılı afiş olduğu için yazı resmin üstüne binmez, kartın beyaz kısmında durur.
    Anasayfa, şehir ve blog listesi sayfalarında kullanılır (App\View\Components\MostPopularActivities).
--}}
@if ($activities->isNotEmpty())
    @php
        $typeLabel = fn ($type) => match ($type) {
            'pass' => __('most-popular-activities.category_pass'),
            'package' => __('most-popular-activities.category_package'),
            default => __('most-popular-activities.category_product'),
        };
        $sliderId = 'popular-' . \Illuminate\Support\Str::random(6);
        // "Tüm aktiviteler" kartının resmi panelden (Yönetim › Bölümler › Popüler aktiviteler); yoksa varsayılan
        $allSettings = \App\Models\Setting::many(['popular_all_image', 'popular_overlay']);
        $allImage = $allSettings['popular_all_image'] ? \App\Models\ActivityImage::mediaUrl($allSettings['popular_all_image']) : asset('images/all-activities-cover.webp');
        // Karartma panelden (Yönetim › Bölümler › Popüler aktiviteler): Az / Orta / Çok
        [$allOpacity, $allShade] = [
            'soft' => ['opacity-80', 'from-slate-950/90 via-slate-950/30 to-transparent'],
            'medium' => ['opacity-50', 'from-slate-950 via-slate-950/60 to-transparent'],
            'strong' => ['opacity-30', 'from-slate-950 via-slate-950/80 to-slate-950/40'],
        ][in_array($allSettings['popular_overlay'], ['soft', 'medium', 'strong'], true) ? $allSettings['popular_overlay'] : 'medium'];
    @endphp

    <section class="{{ $color }} relative pt-16 pb-8 md:pt-24 md:pb-14" x-data="{
            atStart: true, atEnd: false,
            update() { const s = this.$refs.track; const rtl = getComputedStyle(s).direction === 'rtl'; const x = Math.abs(s.scrollLeft);
                this.atStart = x < 8; this.atEnd = x + s.clientWidth >= s.scrollWidth - 8 },
            go(dir) { const s = this.$refs.track; const card = s.querySelector('li'); const rtl = getComputedStyle(s).direction === 'rtl';
                s.scrollBy({ left: (card.offsetWidth + 20) * dir * (rtl ? -1 : 1), behavior: 'smooth' }) },
        }" x-init="update()">
        <div class="mx-auto max-w-7xl px-4">

            {{-- BAŞLIK --}}
            <div class="flex flex-col gap-6 md:flex-row md:items-end md:justify-between">
                <div class="min-w-0 max-w-4xl">
                    <p class="inline-flex items-center gap-2 text-[11px] font-bold uppercase tracking-[0.2em] text-[#C62E2E]">
                        <span class="h-px w-6 bg-[#C62E2E]"></span>
                        {{ $heading['eyebrow'] ?? __('most-popular-activities.eyebrow') }}
                    </p>
                    <h2 class="mt-3 text-3xl font-extrabold leading-[1.08] tracking-[-0.025em] text-slate-900 sm:text-4xl lg:text-[44px]">
                        {{ $heading['title'] ?? __('most-popular-activities.title_prefix') }}
                        <span class="text-[#C62E2E]">{{ $heading['highlight'] ?? __('most-popular-activities.title_highlight') }}</span>
                    </h2>
                    <p class="mt-3 text-base text-slate-600 md:text-lg">{{ $heading['desc'] ?? __('most-popular-activities.desc') }}</p>
                </div>

                <div class="flex shrink-0 items-center gap-3">
                    <a href="{{ \App\Support\PageSections::activitiesLink() }}" class="group inline-flex items-center gap-1.5 text-sm font-semibold text-slate-900">
                        <span class="border-b border-slate-300 pb-0.5 transition group-hover:border-slate-900">{{ __('most-popular-activities.see_all') }}</span>
                        <span aria-hidden="true" class="transition-transform group-hover:translate-x-1 rtl:rotate-180 rtl:group-hover:-translate-x-1">→</span>
                    </a>
                    <div class="ml-2 hidden items-center gap-2 md:flex">
                        @foreach ([-1 => ['prev', 'M15 18l-6-6 6-6'], 1 => ['next', 'M9 6l6 6-6 6']] as $dir => [$label, $path])
                            <button type="button" @click="go({{ $dir }})" :disabled="{{ $dir < 0 ? 'atStart' : 'atEnd' }}"
                                aria-label="{{ __('most-popular-activities.' . $label) }}" aria-controls="{{ $sliderId }}"
                                class="grid size-11 place-items-center rounded-full bg-white text-slate-900 ring-1 ring-slate-200 shadow-sm transition
                                       hover:bg-slate-900 hover:text-white hover:ring-slate-900 disabled:pointer-events-none disabled:opacity-35">
                                <svg class="size-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $path }}" /></svg>
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- KARTLAR: içerikle aynı genişlikte (menü/başlık hizası); masaüstünde 4, tablette 2, telefonda 1 + kenar --}}
        <div class="mx-auto max-w-7xl px-4">
        <ul id="{{ $sliderId }}" x-ref="track" @scroll.debounce.50ms="update()"
            class="-mx-2 mt-8 flex snap-x snap-mandatory scroll-px-2 gap-5 overflow-x-auto px-2 pb-10 pt-3 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">

            @foreach ($activities as $activity)
                @php $image = $activity->cover; @endphp
                <li class="w-[82%] shrink-0 snap-start sm:w-[calc((100%-1.25rem)/2)] lg:w-[calc((100%-3*1.25rem)/4)]">
                    <a href="{{ route('activities.show', $activity->slug) }}"
                        class="group flex h-full flex-col overflow-hidden rounded-3xl bg-white ring-1 ring-slate-200/80 transition duration-500 ease-[cubic-bezier(0.22,1,0.36,1)]
                               shadow-[0_1px_2px_rgba(15,23,42,0.04),0_14px_36px_-24px_rgba(15,23,42,0.3)]
                               hover:shadow-[0_28px_60px_-28px_rgba(15,23,42,0.45)]">

                        {{-- RESİM --}}
                        <div class="relative aspect-[4/3] overflow-hidden bg-slate-100">
                            @if ($image)
                                <img src="{{ $image->url('sm') }}" alt="{{ $activity->name }}" loading="lazy" decoding="async"
                                    class="h-full w-full object-cover transition duration-700 ease-[cubic-bezier(0.22,1,0.36,1)] group-hover:scale-[1.06]">
                            @endif
                            <div class="pointer-events-none absolute inset-x-0 top-0 h-16 bg-gradient-to-b from-black/30 to-transparent"></div>
                            <div class="absolute inset-x-3 top-3 flex flex-wrap items-center gap-1.5">
                                <span @class(['rounded-full px-2.5 py-1 text-[11px] font-bold uppercase tracking-wide shadow-sm',
                                              'bg-[#C62E2E] text-white' => $activity->type === 'pass', 'bg-white/95 text-slate-900' => $activity->type !== 'pass'])>
                                    {{ $typeLabel($activity->type) }}
                                </span>
                                @if ($activity->audio_guide)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-slate-900/80 px-2.5 py-1 text-[11px] font-semibold text-white backdrop-blur">
                                        <svg class="size-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 15v-3a8 8 0 0 1 16 0v3"/><path d="M4 15a2 2 0 0 1 2-2h1v7H6a2 2 0 0 1-2-2zM20 15a2 2 0 0 0-2-2h-1v7h1a2 2 0 0 0 2-2z"/></svg>
                                        {{ __('most-popular-activities.audio') }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- YAZI --}}
                        <div class="flex flex-1 flex-col p-5">
                            @if ($activity->city)
                                <p class="inline-flex items-center gap-1 text-xs font-medium text-slate-500">
                                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21s-7-6.2-7-11.5A7 7 0 0 1 19 9.5C19 14.8 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/></svg>
                                    {{ $activity->city->name }}
                                </p>
                            @endif
                            <h3 class="mt-1.5 text-base font-bold leading-snug text-slate-900 line-clamp-2">{{ $activity->name }}</h3>
                            <span class="mt-auto flex items-center justify-between border-t border-slate-100 pt-4 text-sm font-semibold">
                                <span class="text-[#C62E2E]">{{ __('most-popular-activities.cta') }}</span>
                                <span class="grid size-8 place-items-center rounded-full bg-slate-100 text-slate-700 transition duration-300 group-hover:bg-[#C62E2E] group-hover:text-white">
                                    <svg class="size-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6" /></svg>
                                </span>
                            </span>
                        </div>
                    </a>
                </li>
            @endforeach

            {{-- SON KART: tüm aktiviteler --}}
            <li class="w-[82%] shrink-0 snap-start sm:w-[calc((100%-1.25rem)/2)] lg:w-[calc((100%-3*1.25rem)/4)]">
                <a href="{{ \App\Support\PageSections::activitiesLink() }}"
                    class="group relative flex h-full min-h-[22rem] flex-col justify-end overflow-hidden rounded-3xl bg-slate-900 p-6 text-white transition duration-500">
                    <img src="{{ $allImage }}" alt="" loading="lazy" decoding="async"
                        class="absolute inset-0 h-full w-full object-cover {{ $allOpacity }} transition duration-700 group-hover:scale-105">
                    <div class="absolute inset-0 bg-gradient-to-t {{ $allShade }}"></div>
                    <div class="relative">
                        <span class="grid size-12 place-items-center rounded-2xl bg-white/15 ring-1 ring-white/20 backdrop-blur transition duration-500 group-hover:bg-[#C62E2E] group-hover:ring-[#C62E2E]">
                            <svg class="size-6 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6" /></svg>
                        </span>
                        <p class="mt-4 text-xl font-extrabold tracking-tight">{{ __('most-popular-activities.cover_title') }}</p>
                        <p class="mt-1.5 text-sm leading-relaxed text-white/75">{{ __('most-popular-activities.cover_text') }}</p>
                        <p class="mt-4 text-sm font-semibold">{{ __('most-popular-activities.cover_cta') }} →</p>
                    </div>
                </a>
            </li>
        </ul>
        </div>
    </section>
@endif
