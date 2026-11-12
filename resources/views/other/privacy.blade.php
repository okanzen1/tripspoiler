@extends('layouts.app')

@section('title', __('privacy-page.meta_title'))
@section('meta_description', __('privacy-page.meta_description'))

{{--
    Gizlilik: sade belge düzeni. Üstte başlık + "Kısaca" kartı, solda yapışkan içindekiler, sağda bölümler.
    Yazılar panelden (Sayfalar › Gizlilik › Hero alanı ve Sayfa içeriği); boşsa dil dosyası (yoksa İngilizce).
    Metinde "- " ile başlayan satırlar liste olur.
--}}
@php
    $locale = app()->getLocale();
    $hero = \App\Support\PageHero::texts('privacy', [
        'eyebrow' => __('privacy-page.eyebrow'), 'title' => __('privacy-page.title'), 'description' => __('privacy-page.desc'),
    ]);
    // Bölümler panelden (Sayfalar › Gizlilik › Bölümler): sıra ve aç/kapa oradan gelir. Kısa anahtar → ikon.
    $icons = [
        'who' => 'users', 'collect' => 'book', 'use' => 'check', 'cookies' => 'info',
        'partners' => 'ticket', 'storage' => 'shield', 'rights' => 'heart', 'changes' => 'clock',
    ];
    $sections = collect(\App\Support\PageSections::content('privacy'))
        ->filter(fn ($key) => str_starts_with($key, 'privacy_'))
        ->map(function ($key) use ($icons, $locale) {
            $short = substr($key, 8);

            return [$short, $icons[$short] ?? 'info', ...array_values(\App\Models\SectionText::texts($key, $locale, [
                'title' => __('privacy-page.' . $short . '_t'), 'description' => __('privacy-page.' . $short . '_d'),
            ]))];
        })->values();
    // "- " satırları liste, diğerleri paragraf
    $blocks = function (string $text) {
        $lines = preg_split('/\n/', str_replace('\n', "\n", trim($text)));
        $out = []; $list = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if (str_starts_with($line, '- ')) { $list[] = substr($line, 2); continue; }
            if ($list) { $out[] = ['ul', $list]; $list = []; }
            if ($line !== '') { $out[] = ['p', $line]; }
        }
        if ($list) { $out[] = ['ul', $list]; }

        return $out;
    };
    // "Kısaca" kutusu ve alttaki iletişim kutusu (Sayfa içeriği › Kısaca kutusu / Alttaki iletişim kutusu)
    $summary = \App\Models\SectionText::texts('privacy_summary', $locale, [
        'title' => __('privacy-page.summary_title'),
        'description' => implode("\n", array_map(fn ($p) => __('privacy-page.' . $p), ['s1', 's2', 's3', 's4'])),
    ]);
    $summaryPoints = array_values(array_filter(array_map(fn ($line) => trim(preg_replace('/^\s*[-•*]\s*/u', '', $line)), preg_split('/\r?\n/', str_replace('\n', "\n", $summary['description'])))));
    $contactBox = \App\Models\SectionText::texts('privacy_contact', $locale, [
        'title' => __('privacy-page.contact_title'), 'description' => __('privacy-page.contact_text'), 'eyebrow' => __('privacy-page.contact_button'),
    ]);
    $updated = \App\Models\SectionText::where('key', 'like', 'privacy_%')->max('updated_at') ?? '2026-11-05';
@endphp

@section('content')
    {{-- ÜST --}}
    <section class="relative overflow-hidden bg-gradient-to-b from-slate-100 to-white">
        <div class="pointer-events-none absolute -left-40 -top-24 size-[42rem] rounded-full bg-[radial-gradient(circle,rgba(14,165,233,0.09),transparent_65%)]" aria-hidden="true"></div>
        <div class="relative mx-auto max-w-7xl px-4 pb-12 pt-6 md:pb-16 md:pt-8">
            <x-breadcrumbs :items="[
                ['label' => __('activity-show.home'), 'url' => route('home')],
                ['label' => __('footer.privacy'), 'url' => route('privacy')],
            ]" />

            <div class="mt-10 grid items-center gap-10 lg:mt-14 lg:grid-cols-12">
                <div class="lg:col-span-7">
                    <span class="inline-flex items-center gap-2 rounded-full bg-sky-100 px-3.5 py-1.5 text-xs font-bold uppercase tracking-[0.14em] text-sky-700">
                        {!! \App\Support\FeatureIcons::svg('shield', 'size-3.5') !!}{{ $hero['eyebrow'] }}
                    </span>
                    <h1 class="mt-5 text-[40px] font-extrabold leading-[1.02] tracking-[-0.04em] text-slate-900 sm:text-6xl">{{ $hero['title'] }}</h1>
                    <p class="mt-5 max-w-xl text-base leading-relaxed text-slate-600 md:text-lg">{{ $hero['description'] }}</p>
                    <p class="mt-5 inline-flex items-center gap-2 text-sm text-slate-500">
                        {!! \App\Support\FeatureIcons::svg('calendar', 'size-4') !!}{{ __('privacy-page.updated') }}: {{ \Illuminate\Support\Carbon::parse($updated)->locale($locale)->isoFormat('D MMMM YYYY') }}
                    </p>
                </div>
                <div class="lg:col-span-5">
                    <div class="rounded-[28px] bg-white p-6 ring-1 ring-slate-200/80 shadow-[0_24px_60px_-36px_rgba(15,23,42,0.4)] sm:p-7">
                        <p class="text-sm font-bold uppercase tracking-[0.14em] text-slate-400">{{ $summary['title'] }}</p>
                        <ul class="mt-4 space-y-3">
                            @foreach ($summaryPoints as $point)
                                <li class="flex items-start gap-3 text-[15px] font-semibold text-slate-800">
                                    <span class="mt-0.5 grid size-6 shrink-0 place-items-center rounded-full bg-slate-900 text-white">
                                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>
                                    </span>
                                    {{ $point }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- BELGE --}}
    <section class="bg-white pb-16 md:pb-24">
        <div class="mx-auto grid max-w-7xl gap-10 px-4 lg:grid-cols-12">
            {{-- içindekiler: tıklanan ve kaydırılan bölüm kırmızı çizgiyle seçili kalır --}}
            @if ($sections->isNotEmpty())
            <nav class="hidden lg:col-span-3 lg:block" aria-label="{{ __('privacy-page.toc') }}"
                x-data="{
                    active: @js($sections->first()[0]),
                    keys: @js($sections->pluck(0)->all()),
                    lock: false,
                    tick: false,
                    lockTimer: null,
                    onScroll: null,
                    init() {
                        const hash = location.hash.slice(1);
                        if (hash && this.keys.includes(hash)) this.active = hash;
                        this.onScroll = () => {
                            if (this.lock || this.tick) return;
                            this.tick = true;
                            requestAnimationFrame(() => { this.tick = false; this.spy(); });
                        };
                        this.spy();
                        window.addEventListener('scroll', this.onScroll, { passive: true });
                        window.addEventListener('resize', this.onScroll, { passive: true });
                    },
                    destroy() {
                        window.removeEventListener('scroll', this.onScroll);
                        window.removeEventListener('resize', this.onScroll);
                        clearTimeout(this.lockTimer);
                    },
                    spy() {
                        const offset = 120;
                        let current = this.keys[0];
                        for (const key of this.keys) {
                            const el = document.getElementById(key);
                            if (el && el.getBoundingClientRect().top <= offset) current = key;
                        }
                        // sayfa sonunda son bölüm seçili kalsın
                        if (window.innerHeight + window.scrollY >= document.documentElement.scrollHeight - 4) {
                            current = this.keys[this.keys.length - 1];
                        }
                        this.active = current;
                    },
                    go(key, event) {
                        event.preventDefault();
                        const el = document.getElementById(key);
                        if (!el) return;
                        // aktif durum hemen değişsin; yumuşak kaydırma sürerken scrollspy devreye girmesin
                        this.active = key;
                        this.lock = true;
                        el.scrollIntoView({ behavior: matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth', block: 'start' });
                        history.replaceState(null, '', '#' + key);
                        clearTimeout(this.lockTimer);
                        this.lockTimer = setTimeout(() => { this.lock = false; this.spy(); }, 800);
                    },
                }">
                <div class="sticky top-8">
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-slate-400">{{ __('privacy-page.toc') }}</p>
                    <ol class="mt-4 space-y-1 border-l border-slate-200">
                        @foreach ($sections as [$key, $icon, $title])
                            <li>
                                <a href="#{{ $key }}" @click="go('{{ $key }}', $event)"
                                    :aria-current="active === '{{ $key }}' ? 'true' : null"
                                    :class="active === '{{ $key }}' ? 'border-[#C62E2E] text-[#C62E2E]' : 'border-transparent text-slate-500 hover:border-[#C62E2E] hover:text-[#C62E2E]'"
                                    class="-ml-px block border-l-2 py-1.5 pl-4 text-sm font-medium transition">
                                    {{ $title }}
                                </a>
                            </li>
                        @endforeach
                    </ol>
                </div>
            </nav>
            @endif

            {{-- bölümler --}}
            <div class="min-w-0 space-y-4 lg:col-span-9">
                @foreach ($sections as [$key, $icon, $title, $text])
                    <article id="{{ $key }}" class="scroll-mt-24 rounded-[24px] p-6 ring-1 ring-slate-200/80 sm:p-8">
                        <div class="flex items-center gap-3">
                            <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-slate-900 text-white">{!! \App\Support\FeatureIcons::svg($icon, 'size-5') !!}</span>
                            <h2 class="text-xl font-extrabold tracking-[-0.015em] text-slate-900 sm:text-2xl">
                                <span class="mr-1 text-slate-300">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span> {{ $title }}
                            </h2>
                        </div>
                        <div class="mt-4 space-y-3 text-[15px] leading-relaxed text-slate-600 sm:pl-[3.25rem]">
                            @foreach ($blocks($text) as [$type, $value])
                                @if ($type === 'ul')
                                    <ul class="space-y-2">
                                        @foreach ($value as $item)
                                            <li class="flex gap-3"><span class="mt-2.5 size-1.5 shrink-0 rounded-full bg-[#C62E2E]"></span><span>{{ $item }}</span></li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p>{{ $value }}</p>
                                @endif
                            @endforeach
                        </div>
                    </article>
                @endforeach

                {{-- iletişim --}}
                <div class="flex flex-col items-start justify-between gap-5 rounded-[24px] bg-slate-900 p-7 text-white sm:flex-row sm:items-center sm:p-8">
                    <div>
                        <p class="text-xl font-extrabold">{{ $contactBox['title'] }}</p>
                        <p class="mt-1 text-white/70">{{ $contactBox['description'] }}</p>
                    </div>
                    <a href="{{ route('contact') }}" class="inline-flex min-h-[48px] shrink-0 items-center gap-2 rounded-full bg-[#C62E2E] px-6 py-3 text-sm font-bold transition hover:bg-[#A82424]">
                        {{ $contactBox['eyebrow'] }}
                        <svg class="size-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- yapısal veri: gizlilik politikası --}}
    @jsonld(\App\Support\Schema::webPage('WebPage', __('privacy-page.meta_title'), __('privacy-page.meta_description'), ['about' => \App\Support\Schema::organizationRef(), 'dateModified' => \Illuminate\Support\Carbon::parse($updated)->toAtomString()]))

    <x-page-sections page="privacy" />
@endsection
