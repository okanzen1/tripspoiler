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
    $sections = collect([
        ['who', 'users'], ['collect', 'book'], ['use', 'check'], ['cookies', 'info'],
        ['partners', 'ticket'], ['storage', 'shield'], ['rights', 'heart'], ['changes', 'clock'],
    ])->map(fn ($s) => [$s[0], $s[1], ...array_values(\App\Models\SectionText::texts('privacy_' . $s[0], $locale, [
        'title' => __('privacy-page.' . $s[0] . '_t'), 'description' => __('privacy-page.' . $s[0] . '_d'),
    ]))]);
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
    $updated = \App\Models\SectionText::where('key', 'like', 'privacy_%')->max('updated_at') ?? '2026-11-05';
@endphp

@section('content')
    {{-- ÜST --}}
    <section class="relative overflow-hidden bg-gradient-to-b from-slate-100 to-white">
        <div class="pointer-events-none absolute -left-24 top-10 size-80 rounded-full bg-emerald-200/40 blur-3xl" aria-hidden="true"></div>
        <div class="relative mx-auto max-w-7xl px-4 pb-12 pt-6 md:pb-16 md:pt-8">
            <x-breadcrumbs :items="[
                ['label' => __('activity-show.home'), 'url' => route('home')],
                ['label' => __('footer.privacy'), 'url' => route('privacy')],
            ]" />

            <div class="mt-10 grid items-center gap-10 lg:mt-14 lg:grid-cols-12">
                <div class="lg:col-span-7">
                    <span class="inline-flex items-center gap-2 rounded-full bg-emerald-100 px-3.5 py-1.5 text-xs font-bold uppercase tracking-[0.14em] text-emerald-800">
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
                        <p class="text-sm font-bold uppercase tracking-[0.14em] text-slate-400">{{ __('privacy-page.summary_title') }}</p>
                        <ul class="mt-4 space-y-3">
                            @foreach (['s1', 's2', 's3', 's4'] as $point)
                                <li class="flex items-start gap-3 text-[15px] font-semibold text-slate-800">
                                    <span class="mt-0.5 grid size-6 shrink-0 place-items-center rounded-full bg-emerald-500 text-white">
                                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>
                                    </span>
                                    {{ __('privacy-page.' . $point) }}
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
            {{-- içindekiler --}}
            <nav class="hidden lg:col-span-3 lg:block" aria-label="{{ __('privacy-page.toc') }}">
                <div class="sticky top-8">
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-slate-400">{{ __('privacy-page.toc') }}</p>
                    <ol class="mt-4 space-y-1 border-l border-slate-200">
                        @foreach ($sections as [$key, $icon, $title])
                            <li>
                                <a href="#{{ $key }}" class="-ml-px block border-l-2 border-transparent py-1.5 pl-4 text-sm font-medium text-slate-500 transition hover:border-[#C62E2E] hover:text-slate-900">
                                    {{ $title }}
                                </a>
                            </li>
                        @endforeach
                    </ol>
                </div>
            </nav>

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
                        <p class="text-xl font-extrabold">{{ __('privacy-page.contact_title') }}</p>
                        <p class="mt-1 text-white/70">{{ __('privacy-page.contact_text') }}</p>
                    </div>
                    <a href="{{ route('contact') }}" class="inline-flex min-h-[48px] shrink-0 items-center gap-2 rounded-full bg-[#C62E2E] px-6 py-3 text-sm font-bold transition hover:bg-[#A82424]">
                        {{ __('privacy-page.contact_button') }}
                        <svg class="size-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <x-page-sections page="privacy" />
@endsection
