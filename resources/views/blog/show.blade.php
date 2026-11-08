@extends('layouts.app')

@section('title', $blog->getTranslation('meta_title', $locale) ?: $blog->title)
@section('meta_description', Str::limit(trim(strip_tags((string) ($blog->getTranslation('meta_description', $locale) ?: $blog->excerpt))), 160))
@section('og_type', 'article')
@if ($blog->coverUrl())
    @section('og_image', $blog->coverUrl())
@endif
@push('meta')
    <meta property="article:published_time" content="{{ ($blog->published_at ?? $blog->created_at)->toIso8601String() }}" />
    <meta property="article:modified_time" content="{{ $blog->updated_at?->toIso8601String() }}" />
@endpush

@push('styles')
    <style>
        @media (prefers-reduced-motion: no-preference) {
            html {
                scroll-behavior: smooth;
            }
        }

        #articleBody :is(h2, h3) {
            scroll-margin-top: 96px;
        }

        #tocList a[aria-current='true'],
        #tocListMobile a[aria-current='true'] {
            border-color: #C62E2E;
            color: #C62E2E;
            font-weight: 600;
        }
    </style>
@endpush

@php
    $coverImage = $blog->coverUrl();
    $publishedAt = $blog->published_at ?? $blog->created_at;
    $activityCount = $blog->activities->count();
    $singleActivity = $activityCount === 1 ? $blog->activities->first() : null;
    $blogIndexUrl = route('blog.index', $blog->city_id ? ['city_id' => $blog->city_id] : []);

    // BlogPosting: Google makale yapısal verisi. Yazar/yayıncı layout'taki Organization'a @id ile bağlanır.
    $pageUrl = \App\Support\Schema::canonical();
    $headline = trim(strip_tags((string) $blog->getTranslation('title', $locale)));
    $schemaDescription = trim(strip_tags((string) ($blog->getTranslation('meta_description', $locale) ?: $blog->excerpt)));
    $articleSchema = array_filter([
        '@context' => 'https://schema.org',
        '@type' => 'BlogPosting',
        '@id' => $pageUrl . '#article',
        // Google başlığı 110 karakterde keser
        'headline' => Str::limit($headline, 110, '…'),
        'name' => $headline,
        'description' => $schemaDescription ?: null,
        'image' => $coverImage ? [$coverImage] : [\App\Support\Schema::logoUrl()],
        'author' => [
            '@type' => 'Organization',
            '@id' => \App\Support\Schema::organizationId(),
            'name' => 'TripSpoiler',
            'url' => \App\Support\Schema::home(),
        ],
        'publisher' => \App\Support\Schema::organizationRef(),
        'datePublished' => $publishedAt->toIso8601String(),
        'dateModified' => ($blog->updated_at && $blog->updated_at->gt($publishedAt) ? $blog->updated_at : $publishedAt)->toIso8601String(),
        'inLanguage' => $locale,
        'url' => $pageUrl,
        'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $pageUrl, 'isPartOf' => ['@id' => \App\Support\Schema::websiteId()]],
        'keywords' => array_values(array_filter((array) $blog->tags)) ? implode(', ', array_filter((array) $blog->tags)) : null,
        'wordCount' => str_word_count(strip_tags((string) $blog->content)) ?: null,
        'contentLocation' => $blog->city ? ['@type' => 'City', 'name' => $blog->city->name] : null,
        'about' => $blog->city ? ['@type' => 'TouristDestination', 'name' => $blog->city->name] : null,
    ], fn ($value) => $value !== null);
@endphp

@section('content')
    {{-- okuma ilerlemesi --}}
    <div class="pointer-events-none fixed inset-x-0 top-0 z-[60] h-[3px]" aria-hidden="true">
        <div id="readingProgress" class="h-full origin-left scale-x-0 bg-[#C62E2E] rtl:origin-right"></div>
    </div>

    {{-- BANNER: sinematik kapak + üzerine binen editoryal künye --}}
    @php
        $previewActivity = $singleActivity ?? $blog->activities->first();
    @endphp
    <section class="relative bg-white">
        <div class="relative h-[280px] overflow-hidden bg-[#0F1420] text-white sm:h-[340px] lg:h-[390px] xl:h-[410px]">
            @if ($coverImage)
                <img src="{{ $coverImage }}" alt="{{ $blog->title }}" fetchpriority="high"
                    class="absolute inset-0 h-full w-full object-cover object-center">
            @else
                <div class="absolute inset-0 opacity-[0.07] [background-image:linear-gradient(to_right,white_1px,transparent_1px),linear-gradient(to_bottom,white_1px,transparent_1px)] [background-size:72px_72px]" aria-hidden="true"></div>
                <div class="absolute inset-0 flex items-center justify-center">
                    <span class="text-[140px] font-extrabold leading-none tracking-[-0.06em] text-white/10">
                        {{ mb_substr($blog->city?->name ?? $blog->title, 0, 1) }}
                    </span>
                </div>
            @endif

            <div class="pointer-events-none absolute inset-x-0 top-0 h-44 bg-gradient-to-b from-[#0F1420]/85 via-[#0F1420]/35 to-transparent" aria-hidden="true"></div>
            <div class="pointer-events-none absolute inset-x-0 bottom-0 h-28 bg-gradient-to-t from-[#0F1420]/60 to-transparent" aria-hidden="true"></div>

            <div class="relative mx-auto flex h-full max-w-7xl flex-col px-4 pt-6 md:pt-8">
                <x-breadcrumbs dark :items="[
                    ['label' => __('activity-show.home'), 'url' => route('home')],
                    ['label' => __('blog-page.blog'), 'url' => route('blog.index')],
                    ...($blog->city ? [['label' => $blog->city->name, 'url' => route('blog.index', ['city_id' => $blog->city_id])]] : []),
                    ['label' => $blog->title, 'url' => url()->current()],
                ]" />
            </div>
        </div>

        <div class="relative z-10 mx-auto -mt-14 max-w-5xl px-4 pb-8 sm:-mt-16 md:-mt-20 md:pb-10">
            <div class="rounded-[24px] bg-white p-6 text-slate-900 ring-1 ring-slate-900/5 shadow-[0_40px_90px_-45px_rgba(15,23,42,0.5)] sm:rounded-[28px] sm:p-8 md:rounded-[32px] md:p-10">
                <div class="grid gap-7 lg:grid-cols-12 lg:gap-10">
                    {{-- sol: başlık ve özet --}}
                    <div class="lg:col-span-8">
                        <div class="flex flex-wrap items-center justify-between gap-x-6 gap-y-2">
                            <span class="inline-flex items-center gap-3 text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">
                                <span class="h-px w-8 shrink-0 bg-[#C62E2E]"></span>
                                {{ $blog->city?->name ?? __('blog-page.blog') }}
                            </span>
                            <span class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">
                                {{ $publishedAt->locale($locale)->isoFormat('D MMMM YYYY') }}
                            </span>
                        </div>

                        <h1 class="mt-4 text-[32px] font-extrabold leading-[1.07] tracking-[-0.04em] text-balance text-slate-900 sm:text-[40px] lg:text-[44px] xl:text-[48px]">
                            {{ $blog->title }}
                        </h1>

                        @if ($blog->excerpt)
                            <p class="mt-4 line-clamp-3 text-[15px] leading-relaxed text-slate-500 md:text-[17px]">{{ $blog->excerpt }}</p>
                        @endif
                    </div>

                    {{-- sağ: aktivite yönlendirmesi + paylaşım --}}
                    <aside class="flex flex-col gap-6 border-t border-slate-100 pt-6 lg:col-span-4 lg:border-t-0 lg:border-l lg:pl-8 lg:pt-0">
                        @if ($previewActivity)
                            <a href="{{ $singleActivity ? $previewActivity->affiliate_url : '#activitiesSection' }}"
                                @if ($singleActivity) target="_blank" rel="noopener noreferrer" @endif
                                class="group block rounded-2xl bg-[#0F1420] p-4 text-white ring-1 ring-white/10 transition hover:bg-[#161D2E] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#C62E2E]">
                                <span class="flex items-center gap-2 text-[10px] font-bold uppercase tracking-[0.18em] text-white/45">
                                    <svg class="size-3.5 shrink-0 text-[#C62E2E]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 8.5A2.5 2.5 0 0 1 6.5 6h11A2.5 2.5 0 0 1 20 8.5v1a2.5 2.5 0 0 0 0 5v1a2.5 2.5 0 0 1-2.5 2.5h-11A2.5 2.5 0 0 1 4 15.5v-1a2.5 2.5 0 0 0 0-5v-1Z"/><path d="M14 9.5v1.5M14 13v1.5"/></svg>
                                    {{ $singleActivity ? __('blog.editors_pick') : __('blog.editors_picks') }}
                                </span>

                                <span class="mt-3 flex items-center gap-3">
                                    @if ($previewActivity->cover)
                                        <span class="relative block size-14 shrink-0 overflow-hidden rounded-xl ring-1 ring-white/15">
                                            <img src="{{ $previewActivity->cover->url('sm') }}" alt="" loading="lazy" decoding="async"
                                                class="h-full w-full object-cover">
                                            @if ($activityCount > 1)
                                                <span class="absolute inset-x-0 bottom-0 bg-[#0F1420]/85 py-0.5 text-center text-[10px] font-bold text-white">+{{ $activityCount - 1 }}</span>
                                            @endif
                                        </span>
                                    @else
                                        <span class="grid size-14 shrink-0 place-items-center rounded-xl bg-white/[0.06] text-white/40 ring-1 ring-white/10">
                                            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 8.5A2.5 2.5 0 0 1 6.5 6h11A2.5 2.5 0 0 1 20 8.5v1a2.5 2.5 0 0 0 0 5v1a2.5 2.5 0 0 1-2.5 2.5h-11A2.5 2.5 0 0 1 4 15.5v-1a2.5 2.5 0 0 0 0-5v-1Z"/></svg>
                                        </span>
                                    @endif
                                    <span class="min-w-0 flex-1 text-[14px] font-bold leading-snug">
                                        <span class="line-clamp-3">
                                            {{ $singleActivity ? $previewActivity->name : $activityCount . ' ' . __('blog.experiences') }}
                                        </span>
                                    </span>
                                </span>

                                <span class="mt-4 flex items-center justify-between gap-3 border-t border-white/10 pt-3">
                                    <span class="text-[11px] font-bold uppercase tracking-[0.14em] text-white/85">
                                        {{ $singleActivity ? __('blog.book_tickets') : __('blog.see_experiences') }}
                                    </span>
                                    <span class="grid size-8 shrink-0 place-items-center rounded-full bg-[#C62E2E] text-white transition group-hover:bg-white group-hover:text-[#0F1420]">
                                        <svg class="size-3.5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                                    </span>
                                </span>
                            </a>
                        @endif

                        <div class="mt-auto flex flex-col gap-3">
                            <span class="inline-flex items-center gap-2 text-[11px] font-bold uppercase tracking-[0.14em] text-slate-500">
                                <svg class="size-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="8.5"/><path d="M12 7.5V12l3 2"/></svg>
                                {{ __('blog-page.min_read', ['count' => $blog->readingMinutes()]) }}
                            </span>

                            <div class="flex items-center gap-2">
                                <button type="button" data-copy-link aria-label="{{ __('blog.copy_link') }}" title="{{ __('blog.copy_link') }}"
                                    class="grid size-9 place-items-center rounded-full bg-[#C62E2E] text-white shadow-sm ring-1 ring-[#C62E2E] transition hover:bg-[#A82424] hover:ring-[#A82424]">
                                    <svg class="size-[15px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M10.5 13.5a4 4 0 0 0 5.7 0l2.3-2.3a4 4 0 0 0-5.7-5.7l-1.3 1.3"/><path d="M13.5 10.5a4 4 0 0 0-5.7 0l-2.3 2.3a4 4 0 0 0 5.7 5.7l1.3-1.3"/></svg>
                                </button>
                                <a href="https://twitter.com/intent/tweet?url={{ urlencode(url()->current()) }}&text={{ urlencode($blog->title) }}" target="_blank" rel="noopener noreferrer" aria-label="X" title="X"
                                    class="grid size-9 place-items-center rounded-full bg-[#0F1420] text-white shadow-sm ring-1 ring-[#0F1420] transition hover:bg-black hover:ring-black">
                                    <svg class="size-[12px]" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18.9 2H22l-6.8 7.8L23 22h-6.3l-4.9-6.4L6.2 22H3l7.3-8.3L3 2h6.4l4.4 5.9L18.9 2Zm-1.1 18h1.7L8.3 3.9H6.5L17.8 20Z"/></svg>
                                </a>
                                <a href="https://wa.me/?text={{ urlencode($blog->title . ' ' . url()->current()) }}" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp" title="WhatsApp"
                                    class="grid size-9 place-items-center rounded-full bg-[#25D366] text-white shadow-sm ring-1 ring-[#25D366] transition hover:bg-[#1DA851] hover:ring-[#1DA851]">
                                    <svg class="size-[15px]" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2a10 10 0 0 0-8.6 15L2 22l5.2-1.4A10 10 0 1 0 12 2Zm0 18.2c-1.5 0-3-.4-4.3-1.2l-.3-.2-3.1.8.8-3-.2-.3A8.2 8.2 0 1 1 12 20.2Zm4.7-6.1c-.3-.1-1.6-.8-1.8-.9-.2-.1-.4-.1-.6.1-.2.3-.7.9-.8 1.1-.2.2-.3.2-.6.1-.3-.1-1.1-.4-2.1-1.3-.8-.7-1.3-1.5-1.4-1.8-.2-.3 0-.4.1-.5l.4-.5.3-.4c.1-.2 0-.4 0-.5 0-.1-.6-1.5-.8-2-.2-.5-.4-.4-.6-.4h-.5c-.2 0-.5.1-.7.3-.2.3-.9.9-.9 2.1s.9 2.4 1 2.6c.1.2 1.7 2.7 4.2 3.7 1.6.7 2.3.7 3.1.6.5-.1 1.6-.7 1.8-1.3.2-.6.2-1.2.2-1.3-.1-.1-.3-.2-.6-.3Z"/></svg>
                                </a>
                            </div>
                        </div>
                    </aside>
                </div>
            </div>
        </div>
    </section>

    {{-- MAKALE: solda içindekiler + paylaş, sağda içerik --}}
    <section class="bg-white py-10 md:py-16">
        <div class="mx-auto grid max-w-7xl gap-10 px-4 lg:grid-cols-12 lg:gap-12">
            {{-- yan sütun --}}
            <aside class="hidden lg:col-span-3 lg:block">
                <div class="space-y-4 lg:sticky lg:top-24">
                    <div id="tocCard" class="hidden rounded-[22px] bg-slate-50 p-5 ring-1 ring-slate-200/70">
                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">{{ __('blog.toc') }}</p>
                        <ol id="tocList" class="mt-3 space-y-0.5 border-s border-slate-200"></ol>
                    </div>

                    <div class="rounded-[22px] bg-white p-5 ring-1 ring-slate-200/70">
                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">{{ __('blog.share') }}</p>
                        <div class="mt-3 flex items-center gap-2">
                            <button type="button" data-copy-link aria-label="{{ __('blog.copy_link') }}" title="{{ __('blog.copy_link') }}"
                                class="grid size-10 place-items-center rounded-full bg-[#C62E2E] text-white shadow-sm ring-1 ring-[#C62E2E] transition hover:bg-[#A82424] hover:ring-[#A82424]">
                                <svg class="size-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M10.5 13.5a4 4 0 0 0 5.7 0l2.3-2.3a4 4 0 0 0-5.7-5.7l-1.3 1.3"/><path d="M13.5 10.5a4 4 0 0 0-5.7 0l-2.3 2.3a4 4 0 0 0 5.7 5.7l1.3-1.3"/></svg>
                            </button>
                            <a href="https://twitter.com/intent/tweet?url={{ urlencode(url()->current()) }}&text={{ urlencode($blog->title) }}" target="_blank" rel="noopener noreferrer" aria-label="X" title="X"
                                class="grid size-10 place-items-center rounded-full bg-[#0F1420] text-white shadow-sm ring-1 ring-[#0F1420] transition hover:bg-black hover:ring-black">
                                <svg class="size-[15px]" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18.9 2H22l-6.8 7.8L23 22h-6.3l-4.9-6.4L6.2 22H3l7.3-8.3L3 2h6.4l4.4 5.9L18.9 2Zm-1.1 18h1.7L8.3 3.9H6.5L17.8 20Z"/></svg>
                            </a>
                            <a href="https://wa.me/?text={{ urlencode($blog->title . ' ' . url()->current()) }}" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp" title="WhatsApp"
                                class="grid size-10 place-items-center rounded-full bg-[#25D366] text-white shadow-sm ring-1 ring-[#25D366] transition hover:bg-[#1DA851] hover:ring-[#1DA851]">
                                <svg class="size-[18px]" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2a10 10 0 0 0-8.6 15L2 22l5.2-1.4A10 10 0 1 0 12 2Zm0 18.2c-1.5 0-3-.4-4.3-1.2l-.3-.2-3.1.8.8-3-.2-.3A8.2 8.2 0 1 1 12 20.2Zm4.7-6.1c-.3-.1-1.6-.8-1.8-.9-.2-.1-.4-.1-.6.1-.2.3-.7.9-.8 1.1-.2.2-.3.2-.6.1-.3-.1-1.1-.4-2.1-1.3-.8-.7-1.3-1.5-1.4-1.8-.2-.3 0-.4.1-.5l.4-.5.3-.4c.1-.2 0-.4 0-.5 0-.1-.6-1.5-.8-2-.2-.5-.4-.4-.6-.4h-.5c-.2 0-.5.1-.7.3-.2.3-.9.9-.9 2.1s.9 2.4 1 2.6c.1.2 1.7 2.7 4.2 3.7 1.6.7 2.3.7 3.1.6.5-.1 1.6-.7 1.8-1.3.2-.6.2-1.2.2-1.3-.1-.1-.3-.2-.6-.3Z"/></svg>
                            </a>
                        </div>
                    </div>
                </div>
            </aside>

            {{-- yazı --}}
            <div class="min-w-0 lg:col-span-9">
                <details id="tocMobile" class="group mb-8 hidden rounded-[20px] bg-slate-50 px-4 py-3.5 ring-1 ring-slate-200/70 lg:hidden">
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-3 text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500 [&::-webkit-details-marker]:hidden">
                        {{ __('blog.toc') }}
                        <svg class="size-4 shrink-0 text-slate-400 transition-transform duration-300 group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                    </summary>
                    <ol id="tocListMobile" class="mt-3 space-y-0.5 border-s border-slate-200"></ol>
                </details>

                <article id="articleBody" class="rich-text quill-content max-w-[46rem]">
                    {!! $blog->content !!}
                </article>

                {{-- yazar / yazı sonu --}}
                <div class="mt-12 max-w-[46rem] rounded-[24px] bg-slate-50 p-6 ring-1 ring-slate-200/70">
                    <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-center gap-4">
                            <span class="grid size-12 shrink-0 place-items-center rounded-2xl bg-white ring-1 ring-slate-200/80">
                                <img src="{{ asset('images/logo-mark.webp') }}" alt="" width="210" height="240" class="h-8 w-auto" loading="lazy">
                            </span>
                            <div class="min-w-0">
                                <p class="text-[15px] font-extrabold tracking-tight text-slate-900">Trip<span class="text-[#C62E2E]">Spoiler</span></p>
                                <p class="mt-0.5 text-[13px] leading-snug text-slate-500">{{ __('footer.tagline') }}</p>
                            </div>
                        </div>
                        <a href="{{ $blogIndexUrl }}"
                            class="inline-flex min-h-[44px] shrink-0 items-center justify-center gap-2 rounded-full bg-slate-900 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-[#C62E2E]">
                            {{ __('blog-page.latest') }}
                            <svg class="size-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                        </a>
                    </div>

                    {{-- mobilde paylaş --}}
                    <div class="mt-5 flex items-center gap-2 border-t border-slate-200/70 pt-4 lg:hidden">
                        <span class="mr-1 text-[11px] font-bold uppercase tracking-[0.16em] text-slate-400">{{ __('blog.share') }}</span>
                        <button type="button" data-copy-link aria-label="{{ __('blog.copy_link') }}"
                            class="grid size-9 place-items-center rounded-full bg-[#C62E2E] text-white shadow-sm ring-1 ring-[#C62E2E] transition hover:bg-[#A82424] hover:ring-[#A82424]">
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.5 13.5a4 4 0 0 0 5.7 0l2.3-2.3a4 4 0 0 0-5.7-5.7l-1.3 1.3"/><path d="M13.5 10.5a4 4 0 0 0-5.7 0l-2.3 2.3a4 4 0 0 0 5.7 5.7l1.3-1.3"/></svg>
                        </button>
                        <a href="https://twitter.com/intent/tweet?url={{ urlencode(url()->current()) }}&text={{ urlencode($blog->title) }}" target="_blank" rel="noopener noreferrer" aria-label="X"
                            class="grid size-9 place-items-center rounded-full bg-[#0F1420] text-white shadow-sm ring-1 ring-[#0F1420] transition hover:bg-black hover:ring-black">
                            <svg class="size-[13px]" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18.9 2H22l-6.8 7.8L23 22h-6.3l-4.9-6.4L6.2 22H3l7.3-8.3L3 2h6.4l4.4 5.9L18.9 2Zm-1.1 18h1.7L8.3 3.9H6.5L17.8 20Z"/></svg>
                        </a>
                        <a href="https://wa.me/?text={{ urlencode($blog->title . ' ' . url()->current()) }}" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp"
                            class="grid size-9 place-items-center rounded-full bg-[#25D366] text-white shadow-sm ring-1 ring-[#25D366] transition hover:bg-[#1DA851] hover:ring-[#1DA851]">
                            <svg class="size-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2a10 10 0 0 0-8.6 15L2 22l5.2-1.4A10 10 0 1 0 12 2Zm0 18.2c-1.5 0-3-.4-4.3-1.2l-.3-.2-3.1.8.8-3-.2-.3A8.2 8.2 0 1 1 12 20.2Zm4.7-6.1c-.3-.1-1.6-.8-1.8-.9-.2-.1-.4-.1-.6.1-.2.3-.7.9-.8 1.1-.2.2-.3.2-.6.1-.3-.1-1.1-.4-2.1-1.3-.8-.7-1.3-1.5-1.4-1.8-.2-.3 0-.4.1-.5l.4-.5.3-.4c.1-.2 0-.4 0-.5 0-.1-.6-1.5-.8-2-.2-.5-.4-.4-.6-.4h-.5c-.2 0-.5.1-.7.3-.2.3-.9.9-.9 2.1s.9 2.4 1 2.6c.1.2 1.7 2.7 4.2 3.7 1.6.7 2.3.7 3.1.6.5-.1 1.6-.7 1.8-1.3.2-.6.2-1.2.2-1.3-.1-.1-.3-.2-.6-.3Z"/></svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- YAZIDAKİ DENEYİMLER --}}
    @if ($singleActivity)
        <section id="activitiesSection" class="scroll-mt-24 bg-white pb-12 md:pb-16">
            <div class="mx-auto max-w-7xl px-4">
                <div class="group grid overflow-hidden rounded-[28px] bg-slate-900 text-white lg:grid-cols-2">
                    <a href="{{ $singleActivity->affiliate_url }}" target="_blank" rel="noopener noreferrer"
                        class="relative block min-h-[280px] overflow-hidden sm:min-h-[340px] lg:min-h-[420px]">
                        <img loading="lazy" decoding="async"
                            src="{{ $singleActivity->cover?->url() ?? asset('images/all-activities-cover.webp') }}"
                            alt="{{ $singleActivity->name }}"
                            class="absolute inset-0 h-full w-full object-cover transition duration-700 group-hover:scale-105">
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/70 via-slate-950/10 to-transparent lg:bg-gradient-to-r"></div>
                        <span class="absolute left-5 top-5 rounded-full bg-white/95 px-3 py-1 text-[10px] font-bold uppercase tracking-[0.14em] text-slate-900 backdrop-blur rtl:left-auto rtl:right-5">
                            {{ __('blog.editors_pick') }}
                        </span>
                    </a>

                    <div class="flex flex-col justify-center p-7 sm:p-10">
                        <p class="inline-flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-[0.18em] text-white/45">
                            <svg class="size-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21s7-5.4 7-11a7 7 0 1 0-14 0c0 5.6 7 11 7 11z"/><circle cx="12" cy="10" r="2.5"/></svg>
                            {{ $singleActivity->city?->name }}@if ($singleActivity->city?->country?->name)<span class="text-white/25">·</span>{{ $singleActivity->city->country->name }}@endif
                        </p>
                        <h2 class="mt-3 text-2xl font-extrabold leading-tight tracking-[-0.02em] text-balance sm:text-3xl">{{ $singleActivity->name }}</h2>
                        <a href="{{ $singleActivity->affiliate_url }}" target="_blank" rel="noopener noreferrer"
                            class="mt-7 inline-flex min-h-[48px] w-max items-center gap-2 rounded-full bg-[#C62E2E] px-6 py-3 text-sm font-bold text-white transition hover:bg-[#A82424]">
                            {{ __('blog.explore_experience') }}
                            <svg class="size-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                        </a>
                    </div>
                </div>
            </div>
        </section>
    @elseif ($activityCount > 1)
        <section id="activitiesSection" class="scroll-mt-24 bg-white pb-12 md:pb-16">
            <div class="mx-auto max-w-7xl px-4">
                <div>
                    <p class="inline-flex items-center gap-2 text-[11px] font-bold uppercase tracking-[0.2em] text-slate-500">
                        <span class="h-px w-7 shrink-0 bg-[#C62E2E]"></span>
                        {{ __('blog.editors_picks') }}
                    </p>
                    <h2 class="mt-2 text-2xl font-extrabold tracking-[-0.02em] text-slate-900 md:text-[32px]">
                        {{ $activityCount }} {{ __('blog.experiences') }}
                    </h2>
                </div>

                <div class="mt-6 grid gap-4 sm:mt-8 sm:grid-cols-2 sm:gap-5">
                    @foreach ($blog->activities as $activity)
                        <a href="{{ $activity->affiliate_url }}" target="_blank" rel="noopener noreferrer"
                            class="group flex flex-row overflow-hidden rounded-[20px] bg-white ring-1 ring-slate-200/80 shadow-[0_22px_55px_-40px_rgba(15,23,42,0.45)] transition duration-300 hover:-translate-y-0.5 hover:shadow-[0_30px_70px_-40px_rgba(15,23,42,0.5)] lg:rounded-[26px]">
                            <div class="relative w-[32%] shrink-0 self-stretch overflow-hidden bg-slate-100 sm:w-[38%] lg:w-[42%]">
                                <img loading="lazy" decoding="async"
                                    src="{{ $activity->cover?->url() ?? asset('images/all-activities-cover.webp') }}"
                                    alt="{{ $activity->name }}"
                                    class="absolute inset-0 h-full w-full object-cover transition duration-700 ease-[cubic-bezier(0.22,1,0.36,1)] group-hover:scale-[1.06]">
                                <div class="pointer-events-none absolute inset-0 bg-gradient-to-t from-slate-950/45 via-transparent to-transparent" aria-hidden="true"></div>
                            </div>

                            <div class="flex min-w-0 flex-1 flex-col p-4 sm:p-5 lg:p-6">
                                <p class="inline-flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-[0.16em] text-slate-400">
                                    <svg class="size-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 21s7-5.4 7-11a7 7 0 1 0-14 0c0 5.6 7 11 7 11z"/><circle cx="12" cy="10" r="2.5"/></svg>
                                    {{ $activity->city?->name }}@if ($activity->city?->country?->name)<span class="text-slate-300">·</span>{{ $activity->city->country->name }}@endif
                                </p>

                                <h3 class="mt-1.5 line-clamp-6 text-base font-extrabold leading-snug tracking-[-0.01em] text-slate-900 transition group-hover:text-[#C62E2E] lg:mt-2 lg:line-clamp-3 lg:text-lg">
                                    {{ $activity->name }}
                                </h3>

                                <span class="mt-auto inline-flex items-center gap-2 pt-3 text-[10px] font-bold uppercase tracking-[0.14em] text-[#C62E2E] sm:pt-4 sm:text-[11px] lg:pt-5">
                                    {{ __('blog.book_tickets') }}
                                    <span class="grid size-7 shrink-0 place-items-center rounded-full bg-[#C62E2E] text-white transition group-hover:bg-slate-900">
                                        <svg class="size-3.5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                                    </span>
                                </span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <x-reviews :source="'blog-show'" :source-id="$blog->id" :sectionHeader="true" :reviewSummary="true" :testimonials="true" :color="'bg-white'" />
    <x-faq source="blog" :source-id="$blog->id" />

    {{-- DİĞER REHBERLER --}}
    @if ($related->isNotEmpty())
        <section class="bg-slate-50 py-12 md:py-16">
            <div class="mx-auto max-w-7xl px-4">
                <div class="flex items-end justify-between gap-4">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-slate-400">{{ __('blog.blog') }}</p>
                        <h2 class="mt-2 text-2xl font-extrabold tracking-[-0.02em] text-slate-900 md:text-3xl">
                            {{ $blog->city ? __('blog.related_city', ['city' => $blog->city->name]) : __('blog.related_all') }}
                        </h2>
                    </div>
                    <a href="{{ $blogIndexUrl }}"
                        class="hidden shrink-0 items-center gap-2 text-sm font-bold text-slate-700 transition hover:text-[#C62E2E] sm:inline-flex">
                        {{ __('blog-page.latest') }}
                        <svg class="size-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                    </a>
                </div>

                <div class="mt-7 grid gap-x-4 gap-y-8 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($related as $post)
                        <a href="{{ route('blog.show', $post->slug) }}" class="group block">
                            <div class="relative aspect-[4/3] overflow-hidden rounded-[22px] bg-slate-200 ring-1 ring-slate-900/5">
                                @if ($post->cover_path)
                                    <img src="{{ $post->coverUrl('sm') }}" alt="{{ $post->title }}" loading="lazy" decoding="async"
                                        class="h-full w-full object-cover transition duration-700 ease-[cubic-bezier(0.22,1,0.36,1)] group-hover:scale-[1.06]">
                                @endif
                                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/55 via-transparent to-transparent opacity-80 transition group-hover:opacity-100"></div>
                                @if ($firstTag = ((array) $post->tags)[0] ?? null)
                                    <span class="absolute left-3 top-3 max-w-[85%] truncate rounded-full bg-white/90 px-2.5 py-1 text-[11px] font-bold text-slate-900 backdrop-blur rtl:left-auto rtl:right-3">{{ $firstTag }}</span>
                                @endif
                                <span class="absolute bottom-3 left-3 inline-flex items-center gap-1 text-[11px] font-semibold text-white/90 rtl:left-auto rtl:right-3">
                                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="8.5"/><path d="M12 7.5V12l3 2"/></svg>
                                    {{ __('blog-page.min_read', ['count' => $post->readingMinutes()]) }}
                                </span>
                            </div>
                            <h3 class="mt-3.5 line-clamp-2 text-[17px] font-extrabold leading-snug tracking-[-0.01em] text-slate-900 transition group-hover:text-[#C62E2E]">{{ $post->title }}</h3>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if ($activityCount > 0)
        {{-- MOBİL SABİT ÇUBUK --}}
        <div id="mobileStickyBar" class="fixed inset-x-0 bottom-0 z-50 translate-y-full transition-transform duration-500 lg:hidden">
            <div class="mx-3 mb-3 flex items-center justify-between gap-3 rounded-[20px] bg-white/95 p-3 shadow-[0_18px_50px_-18px_rgba(15,23,42,0.4)] ring-1 ring-slate-200 backdrop-blur">
                <div class="flex min-w-0 items-center gap-3">
                    @if ($singleActivity?->cover)
                        <img src="{{ $singleActivity->cover->url('sm') }}" alt="" class="size-11 shrink-0 rounded-xl object-cover">
                    @endif
                    <div class="min-w-0">
                        <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-slate-400">
                            {{ $singleActivity ? __('blog.editors_pick') : __('blog.editors_picks') }}
                        </p>
                        <p class="truncate text-sm font-bold text-slate-900">
                            {{ $singleActivity ? $singleActivity->name : $activityCount . ' ' . __('blog.experiences') }}
                        </p>
                    </div>
                </div>
                @if ($singleActivity)
                    <a href="{{ $singleActivity->affiliate_url }}" target="_blank" rel="noopener noreferrer"
                @else
                    <a href="#activitiesSection"
                @endif
                    class="inline-flex min-h-[44px] shrink-0 items-center gap-1.5 rounded-full bg-[#C62E2E] px-4 py-2.5 text-xs font-bold text-white transition hover:bg-[#A82424]">
                        {{ __('blog.view') }}
                        <svg class="size-3.5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                    </a>
            </div>
        </div>

        {{-- MASAÜSTÜ SABİT ÇUBUK --}}
        <div id="stickyCtaBar" class="fixed inset-x-0 bottom-0 z-50 hidden translate-y-full transition-transform duration-500 lg:block">
            <div class="border-t border-slate-200/80 bg-white/90 shadow-[0_-16px_40px_-30px_rgba(15,23,42,0.45)] backdrop-blur">
                <div class="mx-auto flex max-w-7xl items-center justify-between gap-6 px-4 py-3.5">
                    <div class="flex min-w-0 items-center gap-4">
                        @if ($singleActivity?->cover)
                            <img src="{{ $singleActivity->cover->url('sm') }}" alt="" class="size-12 shrink-0 rounded-xl object-cover" loading="lazy">
                        @endif
                        <div class="min-w-0">
                            <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-slate-400">
                                {{ $singleActivity ? __('blog.editors_pick') : __('blog.editors_picks') }}
                            </p>
                            <p class="truncate text-sm font-bold text-slate-900 md:text-[15px]">
                                {{ $singleActivity ? $singleActivity->name : $activityCount . ' ' . __('blog.experiences') }}
                            </p>
                        </div>
                    </div>

                    @if ($singleActivity)
                        <a href="{{ $singleActivity->affiliate_url }}" target="_blank" rel="noopener noreferrer"
                    @else
                        <a href="#activitiesSection"
                    @endif
                        class="inline-flex min-h-[44px] shrink-0 items-center gap-2 rounded-full bg-[#C62E2E] px-6 py-2.5 text-sm font-bold text-white transition hover:bg-[#A82424]">
                            {{ __('blog.view') }}
                            <svg class="size-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                        </a>
                </div>
            </div>
        </div>
    @endif

    {{-- kopyalandı bildirimi --}}
    <div id="copyToast" role="status" aria-live="polite"
        class="pointer-events-none fixed bottom-24 left-1/2 z-[60] -translate-x-1/2 translate-y-2 rounded-full bg-slate-900 px-4 py-2 text-xs font-bold text-white opacity-0 shadow-lg transition duration-300">
        {{ __('blog.copied') }}
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const article = document.getElementById('articleBody');
            const progress = document.getElementById('readingProgress');
            const mobileBar = document.getElementById('mobileStickyBar');
            const desktopBar = document.getElementById('stickyCtaBar');
            const copyToast = document.getElementById('copyToast');
            const footer = document.querySelector('footer');
            const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            /* okuma ilerlemesi + sabit çubuklar (footer görününce çubuklar gizlenir) */
            const onScroll = () => {
                const max = document.documentElement.scrollHeight - window.innerHeight;
                if (progress) progress.style.transform = `scaleX(${max > 0 ? Math.min(1, window.scrollY / max) : 0})`;

                const atFooter = footer && footer.getBoundingClientRect().top < window.innerHeight;
                const show = window.scrollY > 600 && !atFooter;
                mobileBar?.classList.toggle('translate-y-full', !show);
                desktopBar?.classList.toggle('translate-y-full', !show);
            };
            window.addEventListener('scroll', onScroll, { passive: true });
            onScroll();

            /* içindekiler: makaledeki h2/h3 başlıklarından üretilir */
            const lists = [document.getElementById('tocList'), document.getElementById('tocListMobile')].filter(Boolean);
            const tocCard = document.getElementById('tocCard');
            const tocMobile = document.getElementById('tocMobile');
            const headings = article ? [...article.querySelectorAll('h2, h3')].filter((h) => h.textContent.trim()) : [];

            if (headings.length >= 1 && lists.length) {
                tocCard?.classList.remove('hidden');
                tocMobile?.classList.remove('hidden');

                const entries = [];

                headings.forEach((heading, index) => {
                    if (!heading.id) {
                        const base = heading.textContent.toLocaleLowerCase()
                            .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
                            .replace(/[^\p{L}\p{N}]+/gu, '-').replace(/^-+|-+$/g, '').slice(0, 60) || `bolum-${index + 1}`;
                        let id = base;
                        let n = 2;
                        while (document.getElementById(id) && document.getElementById(id) !== heading) id = `${base}-${n++}`;
                        heading.id = id;
                    }

                    const anchors = [];

                    lists.forEach((list) => {
                        const li = document.createElement('li');
                        const a = document.createElement('a');
                        a.href = `#${heading.id}`;
                        a.textContent = heading.textContent.trim();
                        a.className = '-ms-px block border-s-2 border-transparent py-1.5 ps-3 text-[13px] font-medium leading-snug text-slate-500 transition hover:border-[#C62E2E] hover:text-[#C62E2E]'
                            + (heading.tagName === 'H3' ? ' ps-6 text-[12.5px]' : '');
                        a.addEventListener('click', (event) => {
                            event.preventDefault();
                            // Önce mobil içindekileri kapat: yüksekliği değişince hedefin konumu kaymasın
                            if (tocMobile?.open) tocMobile.open = false;
                            history.replaceState(null, '', `#${heading.id}`);
                            heading.scrollIntoView({ behavior: reduceMotion ? 'auto' : 'smooth', block: 'start' });
                            setActive(heading);
                        });
                        li.appendChild(a);
                        list.appendChild(li);
                        anchors.push(a);
                    });

                    entries.push({ heading, anchors });
                });

                const setActive = (current) => {
                    entries.forEach(({ heading, anchors }) => {
                        anchors.forEach((a) => {
                            if (heading === current) a.setAttribute('aria-current', 'true');
                            else a.removeAttribute('aria-current');
                        });
                    });
                };

                const spy = () => {
                    let current = headings[0];
                    for (const heading of headings) {
                        if (heading.getBoundingClientRect().top <= 140) current = heading;
                    }
                    if (window.innerHeight + window.scrollY >= document.documentElement.scrollHeight - 4) {
                        current = headings[headings.length - 1];
                    }
                    setActive(current);
                };

                window.addEventListener('scroll', spy, { passive: true });
                spy();

                if (location.hash) {
                    let key = location.hash.slice(1);
                    try {
                        key = decodeURIComponent(key);
                    } catch {}
                    const target = headings.find((h) => h.id === key);
                    if (target) {
                        setActive(target);
                        target.scrollIntoView({ behavior: 'instant', block: 'start' });
                    }
                }
            }

            /* bağlantıyı kopyala */
            document.querySelectorAll('[data-copy-link]').forEach((button) => {
                button.addEventListener('click', async () => {
                    const url = window.location.href;
                    try {
                        await navigator.clipboard.writeText(url);
                    } catch {
                        const field = document.createElement('textarea');
                        field.value = url;
                        field.style.position = 'fixed';
                        field.style.opacity = '0';
                        document.body.appendChild(field);
                        field.select();
                        document.execCommand('copy');
                        field.remove();
                    }
                    if (copyToast) {
                        copyToast.classList.remove('opacity-0', 'translate-y-2');
                        clearTimeout(window.__copyToast);
                        window.__copyToast = setTimeout(() => copyToast.classList.add('opacity-0', 'translate-y-2'), 2200);
                    }
                    button.setAttribute('aria-label', @json(__('blog.copied')));
                });
            });

        });
    </script>

    @jsonld($articleSchema)
@endpush
