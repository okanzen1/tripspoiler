@extends('layouts.app')

@section('title', __('activities-page.meta_title'))
@section('meta_description', __('activities-page.meta_description'))

{{--
    Aktiviteler: koyu banner (şehir + gerçek sayılar), şehir sekmeleri (sadece aktivitesi olanlar), tür filtresi + arama,
    kartlar. Fiyat yok: affiliate; satın alma detay sayfasındaki partner butonuyla.
--}}
@php
    $typeLabel = fn ($type) => match ($type) {
        'pass' => __('activities-page.pass'),
        'package' => __('activities-page.package'),
        default => __('activities-page.product'),
    };
    $typeCounts = $activities->countBy('type');
    // Kart rozeti tekil ("Şehir kartı"), filtre düğmesi çoğul ("Şehir kartları")
    $badge = fn ($type) => match ($type) {
        'pass' => __('most-popular-activities.category_pass'),
        'package' => __('most-popular-activities.category_package'),
        default => __('most-popular-activities.category_product'),
    };
    $titleCity = trans('activities-page.title_city', ['city' => $city?->name]);
    // Hero yazıları ve görünüm: panel (Sayfalar › Aktiviteler › Hero ayarları)
    $hero = \App\Support\PageHero::texts('activities', [
        'eyebrow' => __('activities-page.eyebrow'), 'title' => __('activities-page.title'), 'description' => __('activities-page.desc'),
    ], $city?->name);
    $look = \App\Support\PageHero::visual('activities', $bannerFocus ?? null);
    $overlayClass = ['soft' => 'from-slate-950/70 via-slate-950/40 to-transparent', 'medium' => 'from-slate-950 via-slate-950/80 to-slate-950/30', 'strong' => 'from-slate-950 via-slate-950/90 to-slate-950/60'][$look['overlay']];
    $imageOpacity = ['soft' => 'opacity-80', 'medium' => 'opacity-60', 'strong' => 'opacity-45'][$look['overlay']];
@endphp

@section('content')
    {{-- BANNER: fotoğraflı, solda başlık + şehir seçimi, sağda rakamlar --}}
    <section data-dock-start data-dock-at="0.3" class="relative isolate z-20 bg-slate-950 text-white">
        {{-- arka plan kendi içinde kırpılır; şehir menüsü banner'ın dışına taşabilsin --}}
        <div class="absolute inset-0 -z-10 overflow-hidden" aria-hidden="true">
            <picture>
                @if ($bannerMobile)<source media="(max-width: 767px)" srcset="{{ $bannerMobile }}">@endif
                <img src="{{ $banner }}" alt="" class="absolute inset-0 h-full w-full object-cover {{ $imageOpacity }}" style="object-position: {{ $look['focus'] }}% center">
            </picture>
            <div class="absolute inset-0 bg-gradient-to-r {{ $overlayClass }} rtl:bg-gradient-to-l"></div>
            <div class="absolute inset-x-0 bottom-0 h-1/2 bg-gradient-to-t from-slate-950 to-transparent"></div>
        </div>

        <div class="mx-auto max-w-7xl px-4 pt-6 md:pt-8">
            <x-breadcrumbs dark :items="[
                ['label' => __('activity-show.home'), 'url' => route('home')],
                ['label' => __('activities-page.eyebrow'), 'url' => route('activities.index')],
                ...($city ? [['label' => $city->name, 'url' => route('activities.index', ['city' => $city->slug])]] : []),
            ]" />
        </div>
        <div class="mx-auto grid max-w-7xl gap-10 px-4 pb-14 pt-8 md:pb-20 md:pt-10 lg:grid-cols-12 lg:items-end">
            <div class="lg:col-span-7">
                <p class="inline-flex items-center gap-2 text-[11px] font-bold uppercase tracking-[0.2em] text-white/70">
                    <span class="h-px w-6 bg-[#FF6B6E]"></span>{{ $hero['eyebrow'] }}
                </p>
                <h1 class="mt-4 text-4xl font-extrabold leading-[1.02] tracking-[-0.03em] sm:text-5xl lg:text-[64px]">
                    {{ $hero['title'] }}<br><span class="text-[#FF6B6E]">{{ $titleCity }}</span>
                </h1>
                <p class="mt-5 max-w-xl text-base leading-relaxed text-white/75 md:text-lg">{{ $hero['description'] }}</p>

                {{-- ŞEHİR SEÇİMİ (her zaman görünür) --}}
                @if ($city && $cities->count() === 1)
                    {{-- tek şehir: seçim yok; geniş cam şerit (bayrak, şehir, aktivite sayısı) --}}
                    <div class="mt-8 flex w-full max-w-xl items-center gap-4 rounded-[22px] bg-gradient-to-r from-white/[0.14] to-white/[0.05] p-2.5 pr-3 ring-1 ring-white/15 backdrop-blur-md">
                        <span class="relative grid size-14 shrink-0 place-items-center rounded-2xl bg-gradient-to-br from-[#FF6B6E] to-[#C62E2E] shadow-[0_12px_28px_-10px_rgba(198,46,46,0.9)]">
                            <svg class="size-7 text-white" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.25a7.5 7.5 0 0 0-7.5 7.5c0 5.6 6.4 11.3 6.7 11.55a1.2 1.2 0 0 0 1.6 0c.3-.25 6.7-5.95 6.7-11.55A7.5 7.5 0 0 0 12 2.25Z" opacity=".35"/><circle cx="12" cy="9.75" r="3.25"/></svg>
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block text-[11px] font-bold uppercase tracking-[0.16em] text-white/55">{{ __('activities-page.city') }}</span>
                            <span class="mt-0.5 flex items-center gap-2 truncate text-lg font-bold">
                                {{ $city->name }}@if ($city->country)<span class="font-medium text-white/60">· {{ $city->country->name }}</span>@endif
                                @if ($city->country?->code)<span class="text-xl leading-none" aria-hidden="true">{{ implode('', array_map(fn ($c) => mb_chr(0x1F1E6 + ord($c) - 65), str_split(strtoupper($city->country->code)))) }}</span>@endif
                            </span>
                        </span>
                        <span class="hidden shrink-0 items-center gap-2 rounded-2xl bg-white px-4 py-2.5 text-sm font-bold text-slate-900 sm:inline-flex">
                            <span class="size-2 rounded-full bg-emerald-500 shadow-[0_0_0_4px_rgba(16,185,129,0.2)]"></span>
                            {{ $stats['total'] }} {{ __('activities-page.stat_total') }}
                        </span>
                    </div>
                @elseif ($city)
                    @php
                        $flagOf = fn ($c) => $c->country?->code ? implode('', array_map(fn ($ch) => mb_chr(0x1F1E6 + ord($ch) - 65), str_split(strtoupper($c->country->code)))) : '';
                    @endphp
                    <div x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false" class="relative mt-8 w-full max-w-xl">
                        <button type="button" @click="open = !open" :aria-expanded="open"
                            :class="open ? 'ring-white/35 bg-white/[0.16]' : 'ring-white/15 hover:ring-white/30'"
                            class="flex w-full items-center gap-4 rounded-[22px] bg-gradient-to-r from-white/[0.14] to-white/[0.05] p-2.5 pr-3 text-left ring-1 backdrop-blur-md transition">
                            <span class="grid size-14 shrink-0 place-items-center rounded-2xl bg-gradient-to-br from-[#FF6B6E] to-[#C62E2E] shadow-[0_12px_28px_-10px_rgba(198,46,46,0.9)]">
                                <svg class="size-7 text-white" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.25a7.5 7.5 0 0 0-7.5 7.5c0 5.6 6.4 11.3 6.7 11.55a1.2 1.2 0 0 0 1.6 0c.3-.25 6.7-5.95 6.7-11.55A7.5 7.5 0 0 0 12 2.25Z" opacity=".35"/><circle cx="12" cy="9.75" r="3.25"/></svg>
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="block text-[11px] font-bold uppercase tracking-[0.16em] text-white/55">{{ __('activities-page.choose_city') }}</span>
                                <span class="mt-0.5 flex items-center gap-2 truncate text-lg font-bold">
                                    {{ $city->name }}@if ($city->country)<span class="font-medium text-white/60">· {{ $city->country->name }}</span>@endif
                                    <span class="text-xl leading-none" aria-hidden="true">{{ $flagOf($city) }}</span>
                                </span>
                            </span>
                            <span class="inline-flex shrink-0 items-center gap-2 rounded-2xl bg-white px-4 py-2.5 text-sm font-bold text-slate-900">
                                <span class="hidden sm:inline">{{ __('activities-page.cities_count', ['count' => $cities->count()]) }}</span>
                                <svg class="size-4 transition duration-300" :class="open && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
                            </span>
                        </button>

                        <div x-show="open" x-cloak
                            x-transition:enter="transition duration-200 ease-out" x-transition:enter-start="opacity-0 -translate-y-2 scale-[0.98]" x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                            x-transition:leave="transition duration-150 ease-in" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0 -translate-y-1"
                            class="absolute inset-x-0 top-full z-50 mt-2 origin-top overflow-hidden rounded-3xl bg-white text-slate-900 shadow-[0_30px_70px_-20px_rgba(2,6,23,0.6)] ring-1 ring-slate-200">
                            <p class="border-b border-slate-100 px-5 py-3 text-[11px] font-bold uppercase tracking-[0.16em] text-slate-400">{{ __('activities-page.choose_city') }}</p>
                            <div class="max-h-80 overflow-y-auto p-2">
                                @foreach ($cities as $c)
                                    <a href="{{ route('activities.index', ['city' => $c->slug]) }}" @click="open = false; $dispatch('acts-loading')"
                                        @class(['group flex items-center gap-3.5 rounded-2xl p-2.5 transition', 'bg-slate-50 ring-1 ring-slate-200' => $c->is($city), 'hover:bg-slate-50' => !$c->is($city)])>
                                        <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-slate-100 text-2xl leading-none">{{ $flagOf($c) ?: '📍' }}</span>
                                        <span class="min-w-0 flex-1">
                                            <span class="block truncate text-[15px] font-bold">{{ $c->name }}</span>
                                            <span class="block truncate text-xs text-slate-500">{{ $c->country?->name }} · {{ trans_choice('activities-page.activity_count', $c->activities_count, ['count' => $c->activities_count]) }}</span>
                                        </span>
                                        @if ($c->is($city))
                                            <span class="grid size-7 shrink-0 place-items-center rounded-full bg-[#C62E2E] text-white">
                                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>
                                            </span>
                                        @else
                                            <svg class="size-4 shrink-0 text-slate-300 transition group-hover:translate-x-0.5 group-hover:text-slate-500 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                                        @endif
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- RAKAMLAR: her biri kendi renginde ikonlu cam kart --}}
            @php
                $statCards = [
                    ['total', 'stat_total', 'from-[#FF6B6E] to-[#C62E2E]', 'bg-[#FF6B6E]', '<path d="M9 4.5v15M15 4.5v15" opacity=".35"/><path d="M4 7.5a3 3 0 0 0 0 6v3a1.5 1.5 0 0 0 1.5 1.5h13A1.5 1.5 0 0 0 20 16.5v-3a3 3 0 0 1 0-6v-1A1.5 1.5 0 0 0 18.5 5h-13A1.5 1.5 0 0 0 4 6.5z"/>'],
                    ['passes', 'stat_passes', 'from-amber-300 to-orange-500', 'bg-amber-400', '<rect x="3" y="5.5" width="18" height="13" rx="2.5"/><path d="M3 10h18" opacity=".35"/><path d="M7 14.5h4"/>'],
                    ['audio', 'stat_audio', 'from-emerald-300 to-teal-600', 'bg-emerald-400', '<path d="M4 14.5v-2a8 8 0 0 1 16 0v2" opacity=".35"/><rect x="3.5" y="13.5" width="4.5" height="6.5" rx="2"/><rect x="16" y="13.5" width="4.5" height="6.5" rx="2"/>'],
                    ['languages', 'stat_languages', 'from-sky-300 to-indigo-600', 'bg-sky-400', '<circle cx="12" cy="12" r="8.5"/><path d="M3.5 12h17M12 3.5c2.6 2.6 3.6 5.4 3.6 8.5s-1 5.9-3.6 8.5c-2.6-2.6-3.6-5.4-3.6-8.5s1-5.9 3.6-8.5z" opacity=".5"/>'],
                ];
                // 0 olan kutu gösterilmez; tek kalan kutu tam genişlik olur
                $statCards = array_values(array_filter($statCards, fn ($card) => ($stats[$card[0]] ?? 0) > 0));
            @endphp
            <dl class="grid grid-cols-2 gap-3 lg:col-span-5">
                @foreach ($statCards as [$k, $label, $grad, $glow, $icon])
                    <div @class(['group relative flex flex-col overflow-hidden rounded-3xl bg-white/[0.08] p-5 ring-1 ring-white/15 backdrop-blur-md transition duration-300 hover:-translate-y-1 hover:bg-white/[0.12]', 'col-span-2' => count($statCards) % 2 === 1 && $loop->first])>
                        <span class="pointer-events-none absolute -right-8 -top-8 size-28 rounded-full {{ $glow }} opacity-25 blur-2xl transition group-hover:opacity-40"></span>
                        <span class="grid size-11 place-items-center rounded-2xl bg-gradient-to-br {{ $grad }} text-white shadow-[0_10px_24px_-10px_rgba(0,0,0,0.6)]">
                            <svg class="size-[22px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">{!! $icon !!}</svg>
                        </span>
                        <dd class="mt-5 text-4xl font-extrabold tracking-tight md:text-[44px]">{{ $stats[$k] }}</dd>
                        <dt class="mt-1 text-sm font-semibold text-white/70">{{ __('activities-page.' . $label) }}</dt>
                    </div>
                @endforeach
            </dl>
        </div>
    </section>

    {{-- Bölümler (panelde Sayfalar › Aktiviteler › Bölümler): aktivite listesi de sıralamada; diğerleri üstüne/altına konabilir --}}
    <x-page-sections page="activities" :city-id="$city?->id" review-source="activity">
        <x-slot:main>
        {{-- LİSTE --}}
        <section id="activities-list" class="bg-slate-50 py-10 md:py-14"
            x-init="$nextTick(() => reveal())" @acts-loading.window="loading = true"
            x-data="{
                tab: 'all', type: 'all', query: '', q: '', loading: false, timer: null, queue: [],
                // Filtre değişince kısa bir yükleniyor (iskelet kartlar), sonra kartlar sırayla belirir
                apply(fn, ms = 420) {
                    // Arka arkaya gelen değişiklikler birikir, hiçbiri kaybolmaz
                    this.loading = true;
                    this.queue.push(fn);
                    clearTimeout(this.timer);
                    this.timer = setTimeout(() => { this.queue.splice(0).forEach(f => f()); this.loading = false; this.$nextTick(() => this.reveal()) }, ms);
                },
                pick(t) { if (t === this.tab) return; this.tab = t; this.apply(() => this.type = t) },
                reveal() {
                    [...this.$refs.grid.children].filter(el => this.match(el)).forEach((el, i) => {
                        el.classList.remove('card-in'); void el.offsetWidth;
                        el.style.setProperty('--d', Math.min(i, 11) * 45 + 'ms');
                        el.classList.add('card-in');
                    });
                },
                norm(t) { return String(t).toLocaleLowerCase('tr').replace(/ı/g, 'i').normalize('NFD').replace(/[̀-ͯ]/g, '') },
                match(el) {
                    const words = this.norm(this.q).split(/\s+/).filter(Boolean);
                    return (this.type === 'all' || el.dataset.type === this.type) && words.every(w => el.dataset.search.includes(w));
                },
                get shown() { return [...this.$refs.grid.children].filter(el => this.match(el)).length },
            }">
            <div class="mx-auto max-w-7xl px-4">

                @if ($activities->isEmpty())
                    <div class="mx-auto max-w-lg rounded-3xl border-2 border-dashed border-slate-200 bg-white p-10 text-center">
                        <p class="text-xl font-bold text-slate-900">{{ __('activities-page.empty_title') }}</p>
                        <p class="mt-2 text-sm text-slate-500">{{ __('activities-page.empty_text') }}</p>
                    </div>
                @else
                    {{-- ARAÇ ÇUBUĞU: ikonlu filtre sekmeleri + uzun arama --}}
                    @php
                        $tabIcons = [
                            'all' => '<rect x="4" y="4" width="7" height="7" rx="1.8"/><rect x="13" y="4" width="7" height="7" rx="1.8"/><rect x="4" y="13" width="7" height="7" rx="1.8"/><rect x="13" y="13" width="7" height="7" rx="1.8"/>',
                            'pass' => '<rect x="3" y="6" width="18" height="12" rx="2.5"/><path d="M3 10h18M7 14.5h4"/>',
                            'product' => '<circle cx="12" cy="12" r="8.5"/><path d="m15 9-1.8 4.2L9 15l1.8-4.2z"/>',
                            'package' => '<path d="M4 8.5 12 4l8 4.5v7L12 20l-8-4.5z"/><path d="M4 8.5 12 13l8-4.5M12 13v7"/>',
                        ];
                    @endphp
                    <div class="relative flex flex-col gap-2 overflow-hidden rounded-[22px] bg-white p-2 shadow-[0_18px_44px_-28px_rgba(15,23,42,0.35)] ring-1 ring-slate-200/80 lg:flex-row lg:items-center">
                        <div class="-mx-2 flex shrink-0 gap-1 overflow-x-auto px-2 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden lg:mx-0 lg:px-0">
                            @foreach (array_merge(['all' => $activities->count()], $typeCounts->all()) as $t => $n)
                                <button type="button" @click="pick('{{ $t }}')"
                                    :class="tab === '{{ $t }}' ? 'bg-[#C62E2E] text-white shadow-[0_10px_24px_-10px_rgba(198,46,46,0.7)]' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'"
                                    class="inline-flex shrink-0 items-center gap-2 rounded-2xl px-4 py-3 text-sm font-semibold transition">
                                    <svg class="size-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">{!! $tabIcons[$t] ?? $tabIcons['product'] !!}</svg>
                                    {{ $t === 'all' ? __('activities-page.all') : $typeLabel($t) }}
                                    <span :class="tab === '{{ $t }}' ? 'text-white/75' : 'text-slate-400'" class="text-xs font-bold">{{ $n }}</span>
                                </button>
                            @endforeach
                        </div>
                        <span class="hidden h-8 w-px bg-slate-200 lg:block"></span>
                        <label class="relative flex min-w-0 flex-1 items-center gap-3 rounded-2xl px-3 py-1.5 transition focus-within:bg-slate-50">
                            <span class="grid size-9 shrink-0 place-items-center rounded-xl bg-slate-900 text-white">
                                <svg class="size-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><circle cx="10.5" cy="10.5" r="6"/><path d="m19.5 19.5-4.2-4.2"/></svg>
                            </span>
                            <input type="search" x-model="query" @input.debounce.250ms="apply(() => q = query, 300)" placeholder="{{ __('activities-page.search') }}"
                                class="min-w-0 flex-1 bg-transparent py-2 text-[15px] font-medium text-slate-900 outline-none placeholder:font-normal placeholder:text-slate-400 [&::-webkit-search-cancel-button]:hidden">
                            <button type="button" x-show="query" x-cloak @click="query = ''; apply(() => q = '', 300)" aria-label="✕" class="grid size-8 shrink-0 place-items-center rounded-full text-slate-400 hover:bg-slate-100 hover:text-slate-700">
                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M6 6l12 12M18 6L6 18"/></svg>
                            </button>
                        </label>
                        {{-- yükleniyor çizgisi --}}
                        <span x-show="loading" x-cloak class="acts-progress pointer-events-none absolute inset-x-0 bottom-0 h-[3px]"></span>
                    </div>
                    <p class="mt-5 flex h-5 items-center text-sm text-slate-500">
                        <span x-show="!loading" x-text="@js(__('activities-page.results')).replace(':count', shown)"></span>
                        <span x-show="loading" x-cloak class="inline-flex gap-1" aria-hidden="true">
                            <span class="acts-dot size-1.5 rounded-full bg-[#C62E2E]"></span><span class="acts-dot size-1.5 rounded-full bg-[#C62E2E] [animation-delay:.15s]"></span><span class="acts-dot size-1.5 rounded-full bg-[#C62E2E] [animation-delay:.3s]"></span>
                        </span>
                    </p>

                    {{-- İSKELET KARTLAR (filtre değişirken) --}}
                    <ul x-show="loading" x-cloak aria-hidden="true" class="mt-4 grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                        @for ($i = 0; $i < min(8, max(4, $activities->count())); $i++)
                            <li class="overflow-hidden rounded-3xl bg-white ring-1 ring-slate-200/80">
                                <div class="acts-skeleton aspect-[4/3]"></div>
                                <div class="space-y-3 p-5">
                                    <div class="flex gap-1.5"><div class="acts-skeleton h-5 w-16 rounded-md"></div><div class="acts-skeleton h-5 w-20 rounded-md"></div></div>
                                    <div class="acts-skeleton h-4 w-11/12 rounded"></div>
                                    <div class="acts-skeleton h-4 w-2/3 rounded"></div>
                                    <div class="flex items-center justify-between border-t border-slate-100 pt-4"><div class="acts-skeleton h-4 w-16 rounded"></div><div class="acts-skeleton size-8 rounded-full"></div></div>
                                </div>
                            </li>
                        @endfor
                    </ul>

                    {{-- KARTLAR --}}
                    <ul x-ref="grid" x-show="!loading" class="mt-4 grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                        @foreach ($activities as $activity)
                            <li x-show="match($el)" data-type="{{ $activity->type }}"
                                data-search="{{ \Illuminate\Support\Str::lower(\Illuminate\Support\Str::ascii(implode(' ', array_filter((array) $activity->getTranslations('name'))))) }} {{ mb_strtolower($activity->name) }}"
                                x-init="$el.dataset.search = norm($el.dataset.search)">
                                <a href="{{ route('activities.show', $activity->slug) }}"
                                    class="group flex h-full flex-col overflow-hidden rounded-3xl bg-white ring-1 ring-slate-200/80 transition duration-500 ease-[cubic-bezier(0.22,1,0.36,1)]
                                           shadow-[0_1px_2px_rgba(15,23,42,0.04),0_14px_36px_-24px_rgba(15,23,42,0.3)] hover:-translate-y-1.5 hover:shadow-[0_28px_60px_-28px_rgba(15,23,42,0.45)]">
                                    <div class="relative aspect-[4/3] overflow-hidden bg-slate-100">
                                        @if ($activity->cover)
                                            <img src="{{ $activity->cover->url('sm') }}" alt="{{ $activity->name }}" loading="lazy" decoding="async"
                                                class="h-full w-full object-cover transition duration-700 ease-[cubic-bezier(0.22,1,0.36,1)] group-hover:scale-[1.06]">
                                        @endif
                                    </div>
                                    <div class="flex flex-1 flex-col p-5">
                                        <div class="flex flex-wrap items-center gap-1.5">
                                            <span @class(['rounded-md px-2 py-0.5 text-[11px] font-bold uppercase tracking-wide',
                                                          'bg-[#C62E2E] text-white' => $activity->type === 'pass', 'bg-slate-900 text-white' => $activity->type !== 'pass'])>{{ $badge($activity->type) }}</span>
                                            @if ($activity->audio_guide)
                                                <span class="inline-flex items-center gap-1 rounded-md bg-amber-50 px-2 py-0.5 text-[11px] font-semibold text-amber-800 ring-1 ring-amber-200">
                                                    <svg class="size-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 15v-3a8 8 0 0 1 16 0v3"/><path d="M4 15a2 2 0 0 1 2-2h1v7H6a2 2 0 0 1-2-2zM20 15a2 2 0 0 0-2-2h-1v7h1a2 2 0 0 0 2-2z"/></svg>
                                                    {{ __('activities-page.audio') }}
                                                </span>
                                            @endif
                                            <span class="ml-auto text-xs font-medium text-slate-400 rtl:ml-0 rtl:mr-auto">{{ $activity->city?->name }}</span>
                                        </div>
                                        <h3 class="mt-3 text-base font-bold leading-snug text-slate-900 line-clamp-2">{{ $activity->name }}</h3>
                                        <span class="mt-auto flex items-center justify-between border-t border-slate-100 pt-4 text-sm font-semibold">
                                            <span class="text-[#C62E2E]">{{ __('activities-page.details') }}</span>
                                            <span class="grid size-8 place-items-center rounded-full bg-slate-100 text-slate-700 transition duration-300 group-hover:bg-[#C62E2E] group-hover:text-white">
                                                <svg class="size-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                                            </span>
                                        </span>
                                    </div>
                                </a>
                            </li>
                        @endforeach
                    </ul>

                    <div x-show="!loading && shown === 0" x-cloak class="mx-auto mt-6 max-w-md rounded-3xl bg-white p-8 text-center ring-1 ring-slate-200">
                        <p class="font-bold text-slate-900">{{ __('activities-page.no_results') }}</p>
                        <p class="mt-1 text-sm text-slate-500">{{ __('activities-page.no_results_hint') }}</p>
                    </div>

                    {{-- NASIL ÇALIŞIR: satış değil, partnere yönlendirme --}}
                    <div class="mt-12 grid gap-4 rounded-3xl bg-white p-6 ring-1 ring-slate-200/80 sm:grid-cols-3 sm:p-8">
                        <p class="text-lg font-extrabold text-slate-900 sm:col-span-3">{{ __('activities-page.how_title') }}</p>
                        @foreach (['how_1', 'how_2', 'how_3'] as $step)
                            <div class="flex items-start gap-3">
                                <span class="grid size-9 shrink-0 place-items-center rounded-full bg-gradient-to-br from-[#E0393E] to-[#A82424] text-sm font-extrabold text-white">{{ $loop->iteration }}</span>
                                <p class="pt-1.5 text-sm leading-relaxed text-slate-600">{{ __('activities-page.' . $step, ['button' => __('activity-show.cta')]) }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>
        </x-slot:main>
    </x-page-sections>

    {{-- Bu sayfadaki "aktiviteler" bağlantıları (#activities-list) listeye yumuşak kayar --}}
    <script>
        document.addEventListener('click', (event) => {
            const link = event.target.closest('a[href="#activities-list"]');
            if (!link) return;
            event.preventDefault();
            // Telefonda header yapışkan: onun yüksekliği kadar boşluk bırak
            const list = document.getElementById('activities-list');
            const header = document.querySelector('header');
            const offset = header && getComputedStyle(header).position === 'sticky' ? header.offsetHeight : 0;
            window.scrollTo({ top: list.getBoundingClientRect().top + window.scrollY - offset, behavior: 'smooth' });
        });
    </script>

@endsection
