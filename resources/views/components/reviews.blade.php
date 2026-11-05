@props([
    'color' => 'bg-slate-50',
    'source' => null,
    'sourceId' => null,
    'sectionHeader' => true,
    'reviewSummary' => true,
    'testimonials' => true,
])

{{--
    Yorumlar: solda başlık + gerçek ortalama puan ve yorum sayısı + butonlar, sağda kayan yorum kartları.
    Yorum yoksa "ilk yorumu sen yaz" kutusu. Veriler App\View\Components\Reviews; yazma penceresi x-review-modal.
--}}
@php
    // Başlık yazıları panelden (Site ayarları > Bölüm yazıları); yoksa dil dosyası
    $texts = \App\Models\SectionText::texts('reviews', app()->getLocale(), [
        'eyebrow' => __('reviews-section.eyebrow'), 'title' => __('reviews-section.title'),
        'title_highlight' => __('reviews-section.title_highlight'), 'description' => __('reviews-section.desc'),
    ]);
@endphp
@php
    $average = $general['averageRating'];
    $total = $general['count'] ?? $reviews->count();
    $palette = ['from-[#E0393E] to-[#A82424]', 'from-amber-400 to-orange-600', 'from-emerald-400 to-teal-600', 'from-sky-400 to-indigo-600', 'from-fuchsia-400 to-purple-600'];
    $initials = function ($name) {
        $parts = preg_split('/\s+/u', trim((string) $name)) ?: ['?'];
        return mb_strtoupper(mb_substr($parts[0], 0, 1) . (count($parts) > 1 ? mb_substr(end($parts), 0, 1) : ''));
    };
    $stars = fn ($value, $class = 'size-4') => collect(range(1, 5))->map(fn ($i) =>
        '<svg class="' . $class . ' ' . ($i <= round($value) ? 'text-amber-400' : 'text-slate-200') . '" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 3l2.7 5.6 6.1.9-4.4 4.3 1 6.1L12 17l-5.4 2.9 1-6.1-4.4-4.3 6.1-.9z"/></svg>')->join('');
    $readAll = '<form action="' . route('reviews.go') . '" method="POST" class="contents">' . csrf_field()
        . '<input type="hidden" name="source" value="' . e($source) . '"><input type="hidden" name="source_id" value="' . e($sourceId) . '">';
@endphp

<section class="{{ $color }} relative overflow-hidden py-16 md:py-24"
    x-data="{ atStart: true, atEnd: false,
        update() { const s = $refs.track; if (!s) return; const x = Math.abs(s.scrollLeft); this.atStart = x < 8; this.atEnd = x + s.clientWidth >= s.scrollWidth - 8 },
        go(dir) { const s = $refs.track; const c = s.querySelector('li'); if (!c) return; const rtl = getComputedStyle(s).direction === 'rtl';
        s.scrollBy({ left: (c.offsetWidth + 20) * dir * (rtl ? -1 : 1), behavior: 'smooth' }) } }">
    <div class="mx-auto grid max-w-7xl gap-10 px-4 lg:grid-cols-12 lg:gap-12">

        {{-- SOL: BAŞLIK + PUAN --}}
        <div class="lg:col-span-4">
            <p class="inline-flex items-center gap-2 text-[11px] font-bold uppercase tracking-[0.2em] text-[#C62E2E]">
                <span class="h-px w-6 bg-[#C62E2E]"></span>{{ $texts['eyebrow'] }}
            </p>
            @if ($sectionHeader)
                <h2 class="mt-3 text-3xl font-extrabold leading-[1.08] tracking-[-0.025em] text-slate-900 sm:text-4xl lg:text-[44px]">
                    {{ $texts['title'] }} <span class="text-[#C62E2E]">{{ $texts['title_highlight'] }}</span>
                </h2>
                <p class="mt-4 text-base leading-relaxed text-slate-600">{{ $texts['description'] }}</p>
            @endif

            @if ($reviewSummary && $total > 0)
                <div class="mt-7 flex items-center gap-4 rounded-3xl bg-white p-5 ring-1 ring-slate-200/80 shadow-[0_14px_36px_-24px_rgba(15,23,42,0.3)]">
                    <span class="text-5xl font-extrabold tracking-tight text-slate-900">{{ number_format($average, 1) }}</span>
                    <span>
                        <span class="flex gap-0.5">{!! $stars($average, 'size-5') !!}</span>
                        <span class="mt-1 block text-sm text-slate-500">{{ __('reviews-section.based_on', ['count' => $total]) }}</span>
                    </span>
                </div>
            @endif


        </div>

        {{-- SAĞ: YORUMLAR --}}
        <div class="relative min-w-0 lg:col-span-8" x-init="$nextTick(() => update())">
            @if ($testimonials && $reviews->isNotEmpty())
                {{-- ÖNCEKİ / SONRAKİ: kartların iki yanında, belirgin --}}
                @if ($reviews->count() > 1)
                    @foreach ([-1 => ['prev', 'M15 18l-6-6 6-6', 'left-0 -translate-x-1/2 rtl:left-auto rtl:right-0 rtl:translate-x-1/2', 'atStart'], 1 => ['next', 'M9 6l6 6-6 6', 'right-0 translate-x-1/2 rtl:right-auto rtl:left-0 rtl:-translate-x-1/2', 'atEnd']] as $dir => [$label, $path, $pos, $edge])
                        <button type="button" @click="go({{ $dir }})" aria-label="{{ __('reviews-section.' . $label) }}" x-show="!{{ $edge }}" x-transition.opacity
                            class="absolute top-[calc(50%-2rem)] z-20 hidden size-12 -translate-y-1/2 place-items-center rounded-full bg-slate-900 text-white shadow-[0_12px_30px_-8px_rgba(2,6,23,0.55)] ring-4 ring-white transition hover:scale-110 hover:bg-[#C62E2E] sm:grid {{ $pos }}">
                            <svg class="size-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $path }}" /></svg>
                        </button>
                    @endforeach
                @endif
                <ul x-ref="track" @scroll.debounce.50ms="update()" class="-mx-4 flex snap-x snap-mandatory scroll-px-4 gap-5 overflow-x-auto px-4 pb-6 pt-2 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden lg:mx-0 lg:scroll-px-0 lg:px-0">
                    @foreach ($reviews as $review)
                        <li class="w-[85%] shrink-0 snap-start sm:w-[calc((100%-1.25rem)/2)]">
                            <figure class="relative flex h-full flex-col rounded-3xl bg-white p-6 ring-1 ring-slate-200/80 shadow-[0_1px_2px_rgba(15,23,42,0.04),0_14px_36px_-24px_rgba(15,23,42,0.3)] sm:p-7">
                                <svg aria-hidden="true" class="absolute right-6 top-5 size-12 text-[#C62E2E]/10 rtl:left-6 rtl:right-auto" viewBox="0 0 24 24" fill="currentColor"><path d="M9.5 6C6.5 7 4.5 9.6 4.5 13v5h6v-6h-3c0-2 1.2-3.4 2.8-4zM19.5 6c-3 1-5 3.6-5 7v5h6v-6h-3c0-2 1.2-3.4 2.8-4z"/></svg>
                                <span class="flex gap-0.5">{!! $stars($review->rating) !!}</span>
                                <blockquote class="mt-4 flex-1 text-[15px] leading-relaxed text-slate-700 line-clamp-5" dir="auto">{{ $review->comment }}</blockquote>
                                <figcaption class="mt-6 flex items-center gap-3 border-t border-slate-100 pt-5">
                                    <span class="grid size-11 shrink-0 place-items-center rounded-full bg-gradient-to-br {{ $palette[$loop->index % count($palette)] }} text-sm font-bold text-white">{{ $initials($review->name) }}</span>
                                    <span class="min-w-0">
                                        <span class="block truncate font-semibold text-slate-900" dir="auto">{{ $review->name }}</span>
                                        <span class="block text-xs text-slate-500">{{ $review->created_at->translatedFormat('F Y') }}</span>
                                    </span>
                                </figcaption>
                            </figure>
                        </li>
                    @endforeach
                </ul>

                {{-- Butonlar kartların altında, sağda (yorum yoksa yazma butonu boş kutuda) --}}
                <div @class(['mt-2 flex flex-wrap justify-end gap-3', 'hidden' => $total === 0])>
                    <button type="button" x-data @click="$dispatch('open-review')"
                        class="inline-flex items-center gap-2 rounded-xl bg-[#C62E2E] px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-[#C62E2E]/20 transition hover:bg-[#A82424] active:scale-[0.98]">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                        {{ __('reviews-section.write') }}
                    </button>
                    @if ($total > 0)
                        {!! $readAll !!}
                            <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-white px-5 py-3 text-sm font-semibold text-slate-900 ring-1 ring-slate-200 transition hover:ring-slate-400">
                                {{ __('reviews-section.read_all') }} <span aria-hidden="true" class="rtl:rotate-180">→</span>
                            </button>
                        </form>
                    @endif
                </div>
            @else
                {{-- YORUM YOK --}}
                <div class="flex h-full min-h-[16rem] flex-col items-center justify-center rounded-3xl border-2 border-dashed border-slate-200 bg-white/60 p-8 text-center">
                    <span class="flex gap-1">{!! $stars(5, 'size-7') !!}</span>
                    <p class="mt-4 text-xl font-bold text-slate-900">{{ __('reviews-section.empty_title') }}</p>
                    <p class="mt-2 max-w-md text-sm leading-relaxed text-slate-500">{{ __('reviews-section.empty_desc') }}</p>
                    <button type="button" x-data @click="$dispatch('open-review')"
                        class="mt-5 rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#C62E2E]">{{ __('reviews-section.write') }}</button>
                </div>
            @endif
        </div>
    </div>

    <x-review-modal :source="$source ?? null" :sourceId="$sourceId ?? null" />
</section>
