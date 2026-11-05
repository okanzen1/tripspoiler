{{--
    Blog bölümü: solda büyük öne çıkan yazı, sağda iki küçük yazı ve bülten kutusu.
    Veriler App\View\Components\MostPopularBlog (panel > Site ayarları > Blog bölümü).
--}}
@php
    $featured = $blogs->first();
    $others = $blogs->slice(1, 2);
    $minutes = fn ($blog) => __('home-hero.guide_minutes', ['count' => $blog->readingMinutes()]);
@endphp

<section class="relative bg-white py-16 md:py-24">
    <div class="mx-auto max-w-7xl px-4">

        {{-- BAŞLIK --}}
        <div class="flex flex-col gap-6 md:flex-row md:items-end md:justify-between">
            <div class="max-w-2xl">
                @if (filled($texts['eyebrow']))
                    <p class="inline-flex items-center gap-2 text-[11px] font-bold uppercase tracking-[0.2em] text-[#C62E2E]">
                        <span class="h-px w-6 bg-[#C62E2E]"></span>{{ $texts['eyebrow'] }}
                    </p>
                @endif
                <h2 class="mt-3 text-3xl font-extrabold leading-[1.08] tracking-[-0.025em] text-slate-900 sm:text-4xl lg:text-[44px]">
                    {{ $texts['title'] }} <span class="text-[#C62E2E]">{{ $texts['title_highlight'] }}</span>
                </h2>
                @if (filled($texts['description']))
                    <p class="mt-3 text-base text-slate-600 md:text-lg">{{ $texts['description'] }}</p>
                @endif
            </div>
            <a href="{{ route('blog.index') }}" class="group inline-flex shrink-0 items-center gap-1.5 text-sm font-semibold text-slate-900">
                <span class="border-b border-slate-300 pb-0.5 transition group-hover:border-slate-900">{{ __('most-popular-blog.see_all') }}</span>
                <span aria-hidden="true" class="transition-transform group-hover:translate-x-1 rtl:rotate-180 rtl:group-hover:-translate-x-1">→</span>
            </a>
        </div>

        <div class="mt-10 grid gap-5 lg:grid-cols-12 lg:gap-6">

            {{-- ÖNE ÇIKAN --}}
            <a href="{{ route('blog.show', $featured->slug) }}"
                class="group relative isolate flex min-h-[26rem] flex-col justify-end overflow-hidden rounded-3xl bg-slate-900 p-6 text-white sm:p-8 lg:col-span-7 lg:row-span-3 lg:min-h-full">
                @if ($featured->cover_path)
                    <img src="{{ $featured->coverUrl() }}" alt="{{ $featured->title }}" loading="lazy" decoding="async"
                        class="absolute inset-0 -z-10 h-full w-full object-cover transition duration-700 ease-[cubic-bezier(0.22,1,0.36,1)] group-hover:scale-105">
                @endif
                <div class="absolute inset-0 -z-10 bg-gradient-to-t from-slate-950 via-slate-950/55 to-slate-950/5"></div>

                <div class="flex flex-wrap items-center gap-2 text-xs font-semibold">
                    <span class="rounded-full bg-[#C62E2E] px-2.5 py-1 uppercase tracking-wide">{{ __('most-popular-blog.featured') }}</span>
                    @if ($featured->city)
                        <span class="rounded-full bg-white/15 px-2.5 py-1 backdrop-blur">{{ $featured->city->name }}</span>
                    @endif
                    <span class="text-white/75">{{ $minutes($featured) }} · {{ __('home-hero.guide_unit') }}</span>
                </div>
                <h3 class="mt-4 max-w-xl text-2xl font-extrabold leading-tight tracking-tight sm:text-3xl">{{ $featured->title }}</h3>
                @if (filled($featured->excerpt))
                    <p class="mt-3 max-w-xl text-sm leading-relaxed text-white/80 line-clamp-2 sm:text-base">{{ $featured->excerpt }}</p>
                @endif
                <span class="mt-6 inline-flex w-fit items-center gap-2 rounded-xl bg-white px-5 py-3 text-sm font-semibold text-slate-900 transition group-hover:bg-[#C62E2E] group-hover:text-white">
                    {{ __('most-popular-blog.read') }}
                    <span aria-hidden="true" class="transition-transform group-hover:translate-x-1 rtl:rotate-180">→</span>
                </span>
            </a>

            {{-- DİĞER YAZILAR --}}
            @foreach ($others as $blog)
                <a href="{{ route('blog.show', $blog->slug) }}"
                    class="group flex gap-4 rounded-3xl bg-white p-3 ring-1 ring-slate-200/80 transition duration-500 ease-[cubic-bezier(0.22,1,0.36,1)]
                           shadow-[0_1px_2px_rgba(15,23,42,0.04),0_14px_36px_-24px_rgba(15,23,42,0.3)]
                           hover:-translate-y-1 hover:shadow-[0_24px_50px_-26px_rgba(15,23,42,0.4)] lg:col-span-5">
                    <span class="relative aspect-[4/3] w-32 shrink-0 self-start overflow-hidden rounded-2xl bg-slate-100 sm:w-40">
                        @if ($blog->cover_path)
                            <img src="{{ $blog->coverUrl('sm') }}" alt="" loading="lazy" decoding="async" class="h-full w-full object-cover transition duration-700 group-hover:scale-105">
                        @endif
                    </span>
                    <span class="flex min-w-0 flex-col py-1 pr-2">
                        <span class="text-xs font-medium text-slate-500">{{ $blog->city?->name }}{{ $blog->city ? ' · ' : '' }}{{ $minutes($blog) }}</span>
                        <span class="mt-1 text-base font-bold leading-snug text-slate-900 line-clamp-2">{{ $blog->title }}</span>
                        @if (filled($blog->excerpt))
                            <span class="mt-1 text-sm leading-relaxed text-slate-500 line-clamp-2 max-sm:hidden">{{ $blog->excerpt }}</span>
                        @endif
                        <span class="mt-auto pt-2 text-sm font-semibold text-[#C62E2E]">{{ __('most-popular-blog.read') }} →</span>
                    </span>
                </a>
            @endforeach

            {{-- BÜLTEN --}}
            <div class="lg:col-span-5">
                <x-newsletter-subscribe :texts="$newsletter" />
            </div>
        </div>
    </div>
</section>
