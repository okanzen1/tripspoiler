{{--
    Şehir rehberi alanı (Gezilecek yerler, Yeme içme…): solda yapışkan başlık bloğu, sağda kapak + yazı.
    Yazı panel editöründen (temizlenmiş HTML); resimler editörde seçilen boyutta.
--}}
@props(['section', 'color' => 'bg-white', 'number' => 1, 'total' => 1])

@php
    $topic = $section->topic;
    $cover = $section->imageUrl();
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
                <div class="rich-text">{!! $section->content !!}</div>
            </div>
        </div>
    </div>
</section>
