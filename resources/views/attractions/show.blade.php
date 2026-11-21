@extends('layouts.app')

@section('title', $metaTitle)
@section('meta_description', $metaDescription)
@if ($attraction->image_path)
    @section('og_image', $attraction->imageUrl())
@endif

{{-- Yer sayfası (panel: Yerler): biletler (passler ve kombolar önce), ziyaret bilgileri, anlatım, gitmeden önce, SSS, diğer yerler --}}
@php
    $city = $attraction->city;
    $info = array_filter([
        ['clock', __('attraction-page.opening_hours'), $attraction->opening_hours],
        ['calendar', __('attraction-page.closed_days'), $attraction->closed_days],
        ['route', __('attraction-page.getting_there'), $attraction->getting_there],
        ['shield', __('attraction-page.dress_code'), $attraction->dress_code],
    ], fn ($row) => trim((string) $row[2]) !== '');
    $typeLabel = fn ($type) => match ($type) {
        'pass' => __('most-popular-activities.category_pass'),
        'package' => __('most-popular-activities.category_package'),
        default => __('most-popular-activities.category_product'),
    };
    $goodToKnow = $attraction->goodToKnow();
@endphp

@section('content')
    {{-- BANNER --}}
    <section class="relative isolate overflow-hidden bg-slate-950 text-white">
        @if ($attraction->image_path)
            <img src="{{ $attraction->imageUrl() }}" alt="{{ $attraction->name }}" fetchpriority="high" class="absolute inset-0 -z-10 h-full w-full object-cover opacity-90" style="object-position: {{ $attraction->image_focus ?? 50 }}% center">
        @endif
        <div class="absolute inset-0 -z-10 bg-gradient-to-r from-slate-950/90 via-slate-950/55 to-slate-950/10 rtl:bg-gradient-to-l"></div>
        <div class="absolute inset-x-0 bottom-0 -z-10 h-1/2 bg-gradient-to-t from-slate-950/80 to-transparent"></div>
        <div class="mx-auto max-w-7xl px-4 pb-14 pt-6 md:pb-20 md:pt-8">
            <x-breadcrumbs dark :items="[
                ['label' => __('activity-show.home'), 'url' => route('home')],
                ...($city ? [['label' => $city->name, 'url' => route('cities.show', ['slug' => $city->slug])]] : []),
                ['label' => $attraction->name, 'url' => route('attractions.show', $attraction->slug)],
            ]" />
            <div class="mt-24 max-w-3xl md:mt-32">
                <p class="inline-flex items-center gap-2 text-[11px] font-bold uppercase tracking-[0.2em] text-white/75">
                    <span class="h-px w-6 bg-[#FF6B6E]"></span>{{ __('attraction-page.eyebrow', ['city' => $city?->name]) }}
                </p>
                <h1 class="mt-4 text-balance text-4xl font-extrabold leading-[1.05] tracking-[-0.03em] sm:text-5xl lg:text-6xl">{{ $attraction->name }}</h1>
                @if ($attraction->summary)
                    <p class="mt-4 max-w-2xl text-base leading-relaxed text-white/80 md:text-lg">{{ $attraction->summary }}</p>
                @endif
                {{-- Düğmeler + "Takip et" kutusu aynı genişlikte: düğmeler eşit iki parça, yarım kalmaz --}}
                @php $buttons = ($tickets->isNotEmpty() ? 1 : 0) + ($info ? 1 : 0); @endphp
                <div class="mt-7 grid w-full gap-3 sm:w-max sm:min-w-[26rem]">
                    @if ($buttons)
                        <div @class(['grid gap-3', 'grid-cols-2' => $buttons === 2])>
                            @if ($tickets->isNotEmpty())
                                <a href="#tickets" class="inline-flex items-center justify-center gap-2 text-center leading-tight sm:whitespace-nowrap rounded-2xl bg-[#C62E2E] px-5 py-3.5 text-sm font-bold text-white shadow-[0_14px_30px_-12px_rgba(198,46,46,0.8)] transition hover:bg-[#A82424]">
                                    {{ __('attraction-page.see_tickets') }}
                                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
                                </a>
                            @endif
                            @if ($info)
                                <a href="#visit" class="inline-flex items-center justify-center gap-2 text-center leading-tight sm:whitespace-nowrap rounded-2xl bg-white/10 px-5 py-3.5 text-sm font-bold text-white ring-1 ring-white/25 backdrop-blur-md transition hover:bg-white hover:text-slate-900">{{ __('attraction-page.plan_visit') }}</a>
                            @endif
                        </div>
                    @endif
                    <x-social-follow full />
                </div>
            </div>
        </div>
    </section>

    {{-- ZİYARET BİLGİLERİ: tablo --}}
    @if ($info || $attraction->official_url)
        <section id="visit" class="scroll-mt-24 bg-white pt-12 md:pt-16">
            <div class="mx-auto max-w-7xl px-4">
                <div class="rounded-[28px] bg-slate-50 p-5 ring-1 ring-slate-200/70 sm:p-8">
                    <h2 class="text-2xl font-extrabold tracking-[-0.02em] text-slate-900 sm:text-3xl">{{ __('attraction-page.visit_title', ['name' => $attraction->name]) }}</h2>
                    <table class="mt-6 w-full border-collapse text-start text-[15px]">
                        <caption class="sr-only">{{ __('attraction-page.visit_title', ['name' => $attraction->name]) }}</caption>
                        <tbody>
                            @foreach ($info as [$icon, $label, $value])
                                <tr class="border-b border-slate-200/80 last:border-b-0">
                                    <th scope="row" class="w-[42%] py-3.5 pe-4 text-start align-middle font-semibold text-slate-900 sm:w-1/3">
                                        <span class="flex items-center gap-2.5">
                                            <span class="grid size-8 shrink-0 place-items-center rounded-xl bg-white text-[#C62E2E] shadow-sm ring-1 ring-slate-200/70">{!! \App\Support\FeatureIcons::svg($icon, 'size-4') !!}</span>
                                            {{ $label }}
                                        </span>
                                    </th>
                                    <td class="whitespace-pre-line py-3.5 align-middle leading-relaxed text-slate-600">{{ $value }}</td>
                                </tr>
                            @endforeach
                            @if ($attraction->official_url)
                                <tr>
                                    <th scope="row" class="py-3.5 pe-4 text-start align-middle font-semibold text-slate-900">
                                        <span class="flex items-center gap-2.5">
                                            <span class="grid size-8 shrink-0 place-items-center rounded-xl bg-white text-[#C62E2E] shadow-sm ring-1 ring-slate-200/70">{!! \App\Support\FeatureIcons::svg('globe', 'size-4') !!}</span>
                                            {{ __('attraction-page.official_site') }}
                                        </span>
                                    </th>
                                    <td class="py-3.5 align-middle"><a href="{{ $attraction->official_url }}" target="_blank" rel="noopener" class="font-semibold text-[#C62E2E] underline underline-offset-4">{{ parse_url($attraction->official_url, PHP_URL_HOST) }} ↗</a></td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                    <p class="mt-4 text-xs text-slate-500">{{ __('attraction-page.check_note') }}</p>
                </div>
            </div>
        </section>
    @endif

    {{-- BİLETLER --}}
    @if ($tickets->isNotEmpty())
        <section id="tickets" class="scroll-mt-24 bg-white py-12 md:py-16">
            <div class="mx-auto max-w-7xl px-4">
                <h2 class="text-2xl font-extrabold tracking-[-0.02em] text-slate-900 sm:text-3xl">{{ __('attraction-page.tickets_title', ['name' => $attraction->name]) }}</h2>
                <p class="mt-2 max-w-2xl text-slate-500">{{ __('attraction-page.tickets_text') }}</p>
                <ul class="mt-7 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($tickets as $ticket)
                        <li>
                            <a href="{{ route('activities.show', $ticket->localSlug()) }}" class="group flex h-full flex-col overflow-hidden rounded-3xl bg-white ring-1 ring-slate-200 transition duration-300 hover:-translate-y-1 hover:shadow-[0_24px_50px_-26px_rgba(15,23,42,0.5)]">
                                <span class="relative block aspect-[16/10] overflow-hidden bg-slate-100">
                                    @if ($img = $ticket->images->first())
                                        <img src="{{ $img->url('sm') }}" alt="{{ $ticket->name }}" loading="lazy" class="h-full w-full object-cover transition duration-700 group-hover:scale-105">
                                    @endif
                                    <span @class(['absolute left-3 top-3 rounded-full px-3 py-1 text-[11px] font-bold rtl:left-auto rtl:right-3', 'bg-[#C62E2E] text-white' => $ticket->type === 'pass', 'bg-white/90 text-slate-900 backdrop-blur' => $ticket->type !== 'pass'])>{{ $typeLabel($ticket->type) }}</span>
                                </span>
                                <span class="flex flex-1 flex-col p-5">
                                    <span class="line-clamp-3 text-[17px] font-bold leading-snug text-slate-900 group-hover:text-[#C62E2E]">{{ $ticket->name }}</span>
                                    <span class="mt-auto flex flex-wrap items-center gap-2 pt-4 text-xs font-semibold text-slate-500">
                                        @if ($ticket->duration)<span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1">{!! \App\Support\FeatureIcons::svg('clock', 'size-3.5') !!}{{ $ticket->duration }}</span>@endif
                                        @if ($ticket->audio_guide)<span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1">{!! \App\Support\FeatureIcons::svg('headphones', 'size-3.5') !!}{{ __('activity-show.audio_yes') }}</span>@endif
                                    </span>
                                </span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif

    {{-- ANLATIM + GİTMEDEN ÖNCE --}}
    @if (trim(strip_tags((string) $attraction->content)) !== '' || $goodToKnow)
        <section class="bg-slate-50 py-12 md:py-16">
            <div class="mx-auto grid max-w-7xl gap-10 px-4 lg:grid-cols-12">
                @if (trim(strip_tags((string) $attraction->content)) !== '')
                    <div @class(['lg:col-span-8' => $goodToKnow, 'lg:col-span-12 lg:max-w-3xl' => !$goodToKnow])>
                        <h2 class="text-2xl font-extrabold tracking-[-0.02em] text-slate-900 sm:text-3xl">{{ __('attraction-page.about_title', ['name' => $attraction->name]) }}</h2>
                        <div class="rich-text mt-5">{!! \App\Support\RichTables::wrap((string) $attraction->content) !!}</div>
                    </div>
                @endif
                @if ($goodToKnow)
                    <aside class="lg:col-span-4">
                        <div class="rounded-3xl bg-amber-50 p-6 ring-1 ring-amber-100 lg:sticky lg:top-24">
                            <h2 class="flex items-center gap-2.5 text-lg font-extrabold tracking-tight text-slate-900">
                                <span class="grid size-8 place-items-center rounded-xl bg-amber-400 text-white">{!! \App\Support\FeatureIcons::svg('info', 'size-4') !!}</span>
                                {{ __('activity-show.important_info') }}
                            </h2>
                            <ul class="mt-4 space-y-2.5">
                                @foreach ($goodToKnow as $item)
                                    <li class="flex items-start gap-3 text-[15px] leading-relaxed text-slate-700"><span class="mt-2.5 size-1.5 shrink-0 rounded-full bg-amber-500"></span>{{ $item }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </aside>
                @endif
            </div>
        </section>
    @endif

    <x-faq source="attraction" :source-id="$attraction->id" />

    {{-- DİĞER YERLER + ŞEHİR REHBERİ --}}
    @if ($others->isNotEmpty() || $city)
        <section class="bg-white py-12 md:py-16">
            <div class="mx-auto max-w-7xl px-4">
                @if ($others->isNotEmpty())
                    <h2 class="text-2xl font-extrabold tracking-[-0.02em] text-slate-900 sm:text-3xl">{{ __('attraction-page.more_places', ['city' => $city?->name]) }}</h2>
                    <ul class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($others as $other)
                            <li>
                                <a href="{{ route('attractions.show', $other->slug) }}" class="group relative isolate flex h-40 flex-col justify-end overflow-hidden rounded-3xl p-5 text-white">
                                    @if ($other->image_path)<img src="{{ $other->imageUrl('sm') }}" alt="" loading="lazy" class="absolute inset-0 -z-10 h-full w-full object-cover transition duration-700 group-hover:scale-105">@endif
                                    <span class="absolute inset-0 -z-10 bg-gradient-to-t from-slate-950/90 via-slate-950/40 to-slate-950/10"></span>
                                    <span class="text-lg font-extrabold">{{ $other->name }}</span>
                                    <span class="text-xs font-semibold text-white/75">{{ __('attraction-page.see_place') }} →</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
                @if ($city)
                    <a href="{{ route('cities.show', ['slug' => $city->slug]) }}" class="mt-8 inline-flex items-center gap-2 rounded-2xl bg-slate-900 px-5 py-3 text-sm font-bold text-white transition hover:bg-slate-800">
                        {{ __('attraction-page.city_guide', ['city' => $city->name]) }} →
                    </a>
                @endif
            </div>
        </section>
    @endif

    {{-- İLGİLİ YAZILAR: bu yerin turlarına bağlı ve aynı şehrin blog yazıları (iç bağlantı) --}}
    @include('partials.related-posts', ['posts' => $relatedPosts, 'city' => $city])
@endsection

@push('scripts')
    @jsonld($schema)
    @if ($itemList)
        @jsonld(\App\Support\Schema::webPage('CollectionPage', $metaTitle, $metaDescription, ['mainEntity' => $itemList]))
    @endif
@endpush
