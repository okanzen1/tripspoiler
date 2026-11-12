{{--
    Telefon/tablet bölüm kılavuzu: sayfanın bölümleri (panelde Sayfalar › sayfa › Bölümler'de açık olanlar, verilen sırayla).
    Sağ altta yuvarlak düğme (sayfa açılınca hep görünür) okunan bölümün ikonunu gösterir; dokununca liste açılır, bölüme kayar. "Başa dön" banner'a çıkar.
    Bölüm içeriği boşsa (ör. soru yok) sayfada çizilmez, kılavuzda da görünmez. Masaüstünde yok (orada yan menü var).
--}}
@props(['page', 'guideSections' => null, 'keys' => null])

@php
    $icons = [
        'top' => '<path d="M12 19V6M6.5 11.5 12 6l5.5 5.5"/><path d="M5 3.5h14"/>',
        'popular' => '<path d="M4 8a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v1.5a2.5 2.5 0 0 0 0 5V16a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-1.5a2.5 2.5 0 0 0 0-5z" fill="currentColor" fill-opacity=".14"/><path d="M4 8a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v1.5a2.5 2.5 0 0 0 0 5V16a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-1.5a2.5 2.5 0 0 0 0-5z"/><path d="M14 6v12" stroke-dasharray="2 2"/>',
        'blog' => '<path d="M12 7c-1.6-1.7-4.3-2.5-8-2.5v13c3.7 0 6.4.8 8 2.5 1.6-1.7 4.3-2.5 8-2.5v-13c-3.7 0-6.4.8-8 2.5z" fill="currentColor" fill-opacity=".14"/><path d="M12 7c-1.6-1.7-4.3-2.5-8-2.5v13c3.7 0 6.4.8 8 2.5 1.6-1.7 4.3-2.5 8-2.5v-13c-3.7 0-6.4.8-8 2.5zM12 7v13"/>',
        'city_entry' => '<path d="M12 21s-7-6.2-7-11.5A7 7 0 0 1 19 9.5C19 14.8 12 21 12 21z" fill="currentColor" fill-opacity=".14"/><path d="M12 21s-7-6.2-7-11.5A7 7 0 0 1 19 9.5C19 14.8 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/>',
        'faq' => '<circle cx="12" cy="12" r="9" fill="currentColor" fill-opacity=".14"/><circle cx="12" cy="12" r="9"/><path d="M9.5 9.2a2.6 2.6 0 1 1 3.6 2.4c-.7.3-1.1.9-1.1 1.6v.6M12 16.8h.01"/>',
        'weather' => '<circle cx="9" cy="9" r="3.4" fill="currentColor" fill-opacity=".14"/><circle cx="9" cy="9" r="3.4"/><path d="M9 2.8v1.4M2.8 9h1.4M4.6 4.6l1 1M13.4 4.6l-1 1"/><path d="M9.5 20h8.6a3.4 3.4 0 0 0 .4-6.8 5 5 0 0 0-9.6-.6A3.7 3.7 0 0 0 9.5 20z" fill="currentColor" fill-opacity=".14"/><path d="M9.5 20h8.6a3.4 3.4 0 0 0 .4-6.8 5 5 0 0 0-9.6-.6A3.7 3.7 0 0 0 9.5 20z"/>',
        'time' => '<circle cx="12" cy="12" r="9" fill="currentColor" fill-opacity=".14"/><circle cx="12" cy="12" r="9"/><path d="M12 7v5.2l3.3 2"/>',
        'reviews' => '<path d="m12 3.5 2.6 5.3 5.9.9-4.2 4.1 1 5.8-5.3-2.8-5.3 2.8 1-5.8-4.2-4.1 5.9-.9z" fill="currentColor" fill-opacity=".14"/><path d="m12 3.5 2.6 5.3 5.9.9-4.2 4.1 1 5.8-5.3-2.8-5.3 2.8 1-5.8-4.2-4.1 5.9-.9z"/>',
        'social' => '<rect x="3.5" y="3.5" width="17" height="17" rx="5" fill="currentColor" fill-opacity=".14"/><rect x="3.5" y="3.5" width="17" height="17" rx="5"/><circle cx="12" cy="12" r="3.8"/><path d="M17 7h.01"/>',
        'steps' => '<path d="M4 6h2M4 12h2M4 18h2M9 6h11M9 12h11M9 18h11"/>',
        'intro' => '<circle cx="12" cy="12" r="9" fill="currentColor" fill-opacity=".14"/><circle cx="12" cy="12" r="9"/><path d="M12 11v5M12 8h.01"/>',
        'features' => '<rect x="3.5" y="5" width="7" height="14" rx="2" fill="currentColor" fill-opacity=".14"/><rect x="3.5" y="5" width="7" height="14" rx="2"/><rect x="13.5" y="5" width="7" height="14" rx="2"/>',
        'about_story' => '<path d="M5 4.5h11a3 3 0 0 1 3 3v12H8a3 3 0 0 1-3-3z" fill="currentColor" fill-opacity=".14"/><path d="M5 4.5h11a3 3 0 0 1 3 3v12H8a3 3 0 0 1-3-3zM9 9h6M9 13h4"/>',
        'about_how' => '<path d="M5 18a2 2 0 1 0 0-4 2 2 0 0 0 0 4zM19 10a2 2 0 1 0 0-4 2 2 0 0 0 0 4z" fill="currentColor" fill-opacity=".14"/><path d="M5 18a2 2 0 1 0 0-4 2 2 0 0 0 0 4zM19 10a2 2 0 1 0 0-4 2 2 0 0 0 0 4zM7 16h5a3 3 0 0 0 3-3v-1a3 3 0 0 1 3-3"/>',
        'about_values' => '<path d="M12 20s-7-4.4-7-10a4 4 0 0 1 7-2.6A4 4 0 0 1 19 10c0 5.6-7 10-7 10z" fill="currentColor" fill-opacity=".14"/><path d="M12 20s-7-4.4-7-10a4 4 0 0 1 7-2.6A4 4 0 0 1 19 10c0 5.6-7 10-7 10z"/>',
        'about_cta' => '<path d="M21 12a8 8 0 0 1-11.6 7.1L4 20l1-4.6A8 8 0 1 1 21 12z" fill="currentColor" fill-opacity=".14"/><path d="M21 12a8 8 0 0 1-11.6 7.1L4 20l1-4.6A8 8 0 1 1 21 12z"/>',
        // sayfanın ana içeriği (aktivite listesi, aktivite, yazı, yorum listesi)
        'main' => match ($page) {
            'activities' => '<path d="M4 8a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v1.5a2.5 2.5 0 0 0 0 5V16a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-1.5a2.5 2.5 0 0 0 0-5z" fill="currentColor" fill-opacity=".14"/><path d="M4 8a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v1.5a2.5 2.5 0 0 0 0 5V16a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-1.5a2.5 2.5 0 0 0 0-5z"/>',
            'reviews' => '<path d="m12 3.5 2.6 5.3 5.9.9-4.2 4.1 1 5.8-5.3-2.8-5.3 2.8 1-5.8-4.2-4.1 5.9-.9z" fill="currentColor" fill-opacity=".14"/><path d="m12 3.5 2.6 5.3 5.9.9-4.2 4.1 1 5.8-5.3-2.8-5.3 2.8 1-5.8-4.2-4.1 5.9-.9z"/>',
            default => '<path d="M6 3h9l4 4v14H6z" fill="currentColor" fill-opacity=".14"/><path d="M6 3h9l4 4v14H6z"/><path d="M15 3v4h4M9 12h7M9 16h5"/>',
        },
    ];
    $svg = fn (string $paths, string $class) => '<svg class="' . $class . '" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $paths . '</svg>';

    $items = [['top', __('section-nav.top'), $svg($icons['top'], 'size-[22px]')]];
    foreach ($keys ?? \App\Support\PageSections::content($page) as $key) {
        if ($key === 'main') {
            $items[] = [$key, __('section-nav.main_' . $page), $svg($icons['main'], 'size-[22px]')];
            continue;
        }
        if (str_starts_with($key, 'guide:')) {
            $guide = $guideSections?->get((int) substr($key, 6));
            if (!$guide) {
                continue;
            }
            $items[] = [$key, $guide->topic->name, \App\Support\FeatureIcons::svg($guide->topic->icon, 'size-[22px]')];
            continue;
        }
        if (isset($icons[$key])) {
            $items[] = [$key, __('section-nav.' . $key), $svg($icons[$key], 'size-[22px]')];
        }
    }
@endphp

@if (count($items) > 2)
    <div x-data="{
            show: false, open: false, enough: false, active: 'top', present: [],
            init() {
                // sayfada gerçekten çizilen (boş olmayan) bölümler
                this.present = [...document.querySelectorAll('[data-nav-section]')].filter((el) => el.offsetHeight > 0).map((el) => el.dataset.navSection);
                this.enough = this.present.length >= 2;
                this.update();
            },
            has(key) { return key === 'top' || this.present.includes(key) },
            update() {
                this.show = true; // sayfa açılır açılmaz görünür (rehber yazılarında da)
                let current = 'top';
                for (const el of document.querySelectorAll('[data-nav-section]')) {
                    if (el.offsetHeight > 0 && el.getBoundingClientRect().top < window.innerHeight * 0.4) current = el.dataset.navSection;
                }
                this.active = current;
            },
            go(key) {
                this.open = false;
                const reduce = matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth';
                if (key === 'top') { window.scrollTo({ top: 0, behavior: reduce }); return; }
                const el = document.querySelector(`[data-nav-section='${key}']`);
                const header = document.querySelector('header');
                const sticky = document.querySelector('nav[aria-label][class*=sticky]');
                const offset = (header && getComputedStyle(header).position === 'sticky' ? header.offsetHeight : 0) + (sticky?.offsetHeight ?? 0);
                if (el) window.scrollTo({ top: el.getBoundingClientRect().top + window.scrollY - offset + 1, behavior: reduce });
            },
        }"
        @scroll.window.throttle.120ms="update()" @resize.window.throttle.200ms="update()" @keydown.escape.window="open = false"
        class="lg:hidden">

        {{-- arka plan (liste açıkken) --}}
        <div x-show="open" x-cloak x-transition.opacity.duration.200ms @click="open = false" class="fixed inset-0 z-[55] bg-slate-950/30 backdrop-blur-[2px]"></div>

        <div x-show="enough && (show || open)" x-cloak
            x-transition:enter="transition duration-300 ease-out" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition duration-200 ease-in" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0 translate-y-4"
            class="fixed bottom-5 right-4 z-[56] flex flex-col items-end gap-3 rtl:left-4 rtl:right-auto">

            {{-- liste --}}
            <nav x-show="open" x-cloak aria-label="{{ __('section-nav.title') }}"
                x-data="{ atTop: true, atEnd: false, scrollable: false, thumbH: 0, thumbY: 0,
                    edges() {
                        const l = this.$refs.list;
                        this.atTop = l.scrollTop < 4; this.atEnd = l.scrollTop + l.clientHeight >= l.scrollHeight - 4;
                        this.scrollable = l.scrollHeight > l.clientHeight + 4;
                        const track = l.clientHeight - 16;
                        this.thumbH = Math.max(28, track * l.clientHeight / l.scrollHeight);
                        this.thumbY = (track - this.thumbH) * (l.scrollTop / Math.max(1, l.scrollHeight - l.clientHeight));
                    } }"
                x-effect="if (open) $nextTick(() => { const l = $refs.list, a = l.querySelector('[data-current]'); if (a) l.scrollTop = a.offsetTop - l.clientHeight / 2 + a.offsetHeight / 2; edges() })"
                x-transition:enter="transition duration-250 ease-[cubic-bezier(0.22,1,0.36,1)]" x-transition:enter-start="opacity-0 scale-95 translate-y-2" x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition duration-150 ease-in" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0 scale-95"
                class="relative flex max-h-[70vh] w-[17rem] origin-bottom-right flex-col overflow-hidden rounded-[26px] bg-white shadow-[0_30px_70px_-20px_rgba(2,6,23,0.55)] ring-1 ring-slate-200 rtl:origin-bottom-left">
                <p class="flex shrink-0 items-center justify-between px-5 pb-2 pt-4 text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">
                    {{ __('section-nav.title') }}
                    <span x-show="!atTop || !atEnd" class="inline-flex items-center gap-1 normal-case tracking-normal text-slate-400">
                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 9l4-4 4 4M8 15l4 4 4-4"/></svg>{{ __('section-nav.scroll') }}
                    </span>
                </p>
                {{-- üstte/altta solma: kaydırılacak yer olduğunu gösterir --}}
                <div x-show="!atTop" x-transition.opacity class="pointer-events-none absolute inset-x-0 top-10 z-10 h-8 bg-gradient-to-b from-white to-transparent"></div>
                <ul x-ref="list" @scroll="edges()" class="relative min-h-0 flex-1 overflow-y-auto overscroll-contain pb-2 pl-2 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden" :class="scrollable ? 'pr-5' : 'pr-2'">
                    {{-- bölümleri birleştiren ince çizgi (yan menüdeki gibi) --}}
                    <span aria-hidden="true" class="absolute bottom-6 left-[29px] top-6 w-px bg-[#C62E2E]/25 rtl:left-auto rtl:right-[29px]"></span>
                    @foreach ($items as [$key, $label, $icon])
                        <li x-show="has(@js($key))" :data-current="active === @js($key) ? '' : null">
                            <button type="button" @click="go(@js($key))"
                                :class="active === @js($key) ? 'bg-red-50' : 'hover:bg-slate-50'"
                                class="relative flex w-full items-center gap-3 rounded-2xl p-1.5 text-left transition rtl:text-right">
                                <span :class="active === @js($key) ? 'bg-[#C62E2E] text-white ring-[#C62E2E] shadow-[0_8px_20px_-8px_rgba(198,46,46,0.8)]' : 'bg-white text-slate-700 ring-slate-200'"
                                    class="grid size-11 shrink-0 place-items-center rounded-full ring-1 transition">{!! $icon !!}</span>
                                <span :class="active === @js($key) ? 'text-[#C62E2E]' : 'text-slate-800'" class="min-w-0 flex-1 truncate text-sm font-bold">{{ $label }}</span>
                            </button>
                        </li>
                    @endforeach
                </ul>
                {{-- sağda her zaman görünen kaydırma çubuğu (telefonlar kendi çubuğunu gizler) --}}
                <div x-show="scrollable" class="pointer-events-none absolute bottom-2 right-1.5 top-11 w-1.5 rounded-full bg-slate-100" aria-hidden="true">
                    <span class="absolute inset-x-0 rounded-full bg-[#C62E2E]/70" :style="`height:${thumbH}px; transform:translateY(${thumbY}px)`"></span>
                </div>
                {{-- altta hafif solma: devamı olduğunu gösterir --}}
                <div x-show="!atEnd" x-transition.opacity class="pointer-events-none absolute bottom-0 left-0 right-4 z-10 h-10 bg-gradient-to-t from-white to-transparent"></div>
            </nav>

            {{-- düğme: okunan bölümün ikonu --}}
            <button type="button" @click="open = !open" :aria-expanded="open" aria-label="{{ __('section-nav.title') }}"
                class="relative grid size-14 place-items-center rounded-full bg-[#C62E2E] text-white shadow-[0_16px_36px_-10px_rgba(198,46,46,0.75)] ring-4 ring-white transition active:scale-95">
                @foreach ($items as [$key, $label, $icon])
                    <span x-show="!open && active === @js($key)" class="absolute inset-0 grid place-items-center [&_svg]:size-6">{!! $icon !!}</span>
                @endforeach
                <svg x-show="open" x-cloak class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M6 6l12 12M18 6L6 18"/></svg>
                <span x-show="!open" class="absolute -left-1 -top-1 grid size-5 place-items-center rounded-full bg-slate-900 text-white ring-2 ring-white rtl:-right-1 rtl:left-auto">
                    <svg class="size-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.6" stroke-linecap="round"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
                </span>
            </button>
        </div>
    </div>
@endif
