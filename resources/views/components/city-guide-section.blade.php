{{--
    Şehir rehberi alanı (Gezilecek yerler, Yeme içme…): solda yapışkan başlık bloğu, sağda kapak + yazı.
    Yazı panel editöründen (temizlenmiş HTML); resimler editörde seçilen boyutta.
--}}
@props(['section', 'color' => 'bg-white', 'number' => 1, 'total' => 1])

@php
    $topic = $section->topic;
    $cover = $section->imageUrl();
    // Kısaltılmış yazının altındaki solma efekti, bölüm arka planıyla aynı renkte olmalı.
    $fade = $color === 'bg-slate-50' ? 'from-slate-50' : 'from-white';
@endphp

<section id="{{ $topic->key }}" data-guide-section="{{ $topic->key }}" class="{{ $color }} scroll-mt-36 py-14 md:py-20 lg:scroll-mt-24">
    <div class="mx-auto max-w-7xl px-4">
        <div class="grid gap-8 lg:grid-cols-12 lg:gap-14">
            <header class="lg:col-span-4">
                <div class="lg:sticky lg:top-28">
                    <div class="flex items-center gap-3">
                        <span class="grid size-12 shrink-0 place-items-center rounded-2xl bg-gradient-to-br from-[#FF6B6E] to-[#C62E2E] text-white shadow-[0_12px_26px_-12px_rgba(198,46,46,0.8)]">
                            {!! \App\Support\FeatureIcons::svg($topic->icon, 'size-6') !!}
                        </span>
                        <div class="min-w-0">
                            <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-[#C62E2E]">{{ $topic->name }}</p>
                            <p class="text-xs font-semibold text-slate-400">{{ str_pad($number, 2, '0', STR_PAD_LEFT) }} / {{ str_pad($total, 2, '0', STR_PAD_LEFT) }}</p>
                        </div>
                    </div>
                    <h2 class="mt-5 text-[28px] font-extrabold leading-[1.15] tracking-[-0.025em] text-slate-900 sm:text-[34px]">{{ $section->title }}</h2>
                    <p class="mt-4 inline-flex items-center gap-1.5 text-sm font-medium text-slate-500">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"><circle cx="12" cy="12" r="8.5"/><path d="M12 7.5V12l3 2"/></svg>
                        {{ __('cities-page.min_read', ['count' => $section->readingMinutes()]) }}
                    </p>
                </div>
            </header>

            <div class="min-w-0 lg:col-span-8">
                @if ($cover)
                    <img src="{{ $cover }}" alt="{{ $section->title }}" loading="lazy" decoding="async"
                        class="mb-8 aspect-[16/9] w-full rounded-[28px] object-cover shadow-[0_30px_60px_-35px_rgba(15,23,42,0.5)]">
                @endif
                {{-- Uzun yazılar kısaltılmış gelir; "Devamını oku" ile açılır. --}}
                <div x-data="{ open: false, long: false }"
                    x-init="$nextTick(() => { long = $refs.body.scrollHeight > 560 })">
                    <div x-ref="body" class="relative overflow-hidden"
                        :class="long && !open ? 'max-h-[420px]' : ''">
                        <div class="rich-text">{!! $section->bodyHtml() !!}</div>
                        <div x-show="long && !open" x-cloak
                            class="pointer-events-none absolute inset-x-0 bottom-0 h-32 bg-gradient-to-t {{ $fade }} to-transparent"></div>
                    </div>
                    <button type="button" x-show="long" x-cloak
                        @click="open = !open; if (!open) $el.closest('section').scrollIntoView({ behavior: 'smooth' })"
                        class="mt-6 inline-flex items-center gap-2 rounded-full bg-[#C62E2E] px-5 py-2.5 text-sm font-bold text-white shadow-[0_10px_24px_-10px_rgba(198,46,46,0.8)] transition hover:bg-[#A82424]">
                        <span x-text="open ? @js(__('cities-page.read_less')) : @js(__('cities-page.read_more'))"></span>
                        <svg class="size-4 transition" :class="open && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>
