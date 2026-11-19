@extends('layouts.app')

@section('title', __('activities-page.meta_title'))
@section('meta_description', __('activities-page.meta_description'))
@if ($banner)
    @section('og_image', $banner)
@endif

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
    // Başlıkta ":city" varsa şehir tam oraya kırmızı gelir (her dilde doğru yerde: "İstanbul'da yapılacak…");
    // yoksa başlığın altına eklenir. İşaretle alınıp yerine konur.
    $cityMark = "\u{E000}";
    $hero = \App\Support\PageHero::texts('activities', [
        'eyebrow' => __('activities-page.eyebrow'), 'title' => __('activities-page.title'), 'description' => __('activities-page.desc'),
    ], $cityMark);
    $cityInTitle = str_contains($hero['title'], $cityMark);
    $heroTitleHtml = str_replace(e($cityMark), '<span class="text-[#FF6B6E]">' . e($city?->name) . '</span>', e($hero['title']));
    $hero['eyebrow'] = str_replace($cityMark, (string) $city?->name, $hero['eyebrow']);
    $hero['description'] = str_replace($cityMark, (string) $city?->name, $hero['description']);
    $look = \App\Support\PageHero::visual('activities', $bannerFocus ?? null);
    // Karartma yalnızca yazıların olduğu solda; sağ taraf ve resim net kalsın
    $overlayClass = ['soft' => 'from-slate-950/60 via-slate-950/20 to-transparent', 'medium' => 'from-slate-950/80 via-slate-950/35 to-transparent', 'strong' => 'from-slate-950/90 via-slate-950/55 to-slate-950/10'][$look['overlay']];
    $imageOpacity = ['soft' => 'opacity-100', 'medium' => 'opacity-95', 'strong' => 'opacity-85'][$look['overlay']];
@endphp

@section('content')
    {{-- BANNER: fotoğraflı, solda başlık + şehir seçimi, sağda rakamlar --}}
    <section data-dock-start data-dock-at="0.3" class="relative isolate z-20 flex min-h-[520px] flex-col bg-slate-950 text-white md:min-h-[600px] lg:min-h-[640px]">
        {{-- arka plan kendi içinde kırpılır; şehir menüsü banner'ın dışına taşabilsin --}}
        <div class="absolute inset-0 -z-10 overflow-hidden" aria-hidden="true">
            <picture>
                @if ($bannerMobile)<source media="(max-width: 767px)" srcset="{{ $bannerMobile }}">@endif
                <img src="{{ $banner }}" alt="" fetchpriority="high" class="absolute inset-0 h-full w-full object-cover {{ $imageOpacity }}" style="object-position: {{ $look['focus'] }}% center">
            </picture>
            <div class="absolute inset-0 bg-gradient-to-r {{ $overlayClass }} rtl:bg-gradient-to-l"></div>
            <div class="absolute inset-x-0 bottom-0 h-2/5 bg-gradient-to-t from-slate-950/85 to-transparent"></div>
            <div class="absolute inset-x-0 top-0 h-28 bg-gradient-to-b from-slate-950/40 to-transparent"></div>
        </div>

        <div class="mx-auto w-full max-w-7xl px-4 pt-6 md:pt-8">
            <x-breadcrumbs dark :items="[
                ['label' => __('activity-show.home'), 'url' => route('home')],
                ['label' => __('activities-page.eyebrow'), 'url' => route('activities.index')],
                ...($city ? [['label' => $city->name, 'url' => route('activities.index', ['city' => $city->slug])]] : []),
            ]" />
        </div>
        {{-- içerik alta oturur: resmin üst kısmı açık kalsın --}}
        <div class="mx-auto mt-auto grid w-full max-w-7xl gap-8 px-4 pb-12 pt-16 md:pb-16 lg:grid-cols-12 lg:items-end">
            <div class="lg:col-span-7">
                <p class="inline-flex items-center gap-2 text-[11px] font-bold uppercase tracking-[0.2em] text-white/70">
                    <span class="h-px w-6 bg-[#FF6B6E]"></span>{{ $hero['eyebrow'] }}
                </p>
                <h1 class="mt-4 text-balance text-4xl font-extrabold leading-[1.08] tracking-[-0.03em] sm:text-5xl lg:text-[64px]">
                    @if ($cityInTitle)
                        {!! $heroTitleHtml !!}
                    @else
                        {{ $hero['title'] }}@if ($city)<span class="mt-1 block text-[#FF6B6E] md:mt-1.5">{{ $titleCity }}</span>@endif
                    @endif
                </h1>
                <p class="mt-3 max-w-xl text-base leading-relaxed text-white/75 md:mt-4 md:text-lg">{{ $hero['description'] }}</p>
                {{-- ŞEHİR SEÇİMİ: başlığın hemen altında, aranabilir liste (components/city-select) --}}
                @if ($cities->isNotEmpty())
                    <x-city-select class="mt-7" :cities="$cities" :current="$city" :url="fn ($c) => route('activities.index', ['city' => $c->slug])" loading-event="acts-loading" />
                @endif
                {{-- DÜĞMELER: seçicinin hemen altında, aynı genişlikte tek sıra; ana düğme listeye kaydırır --}}
                @if ($city)
                    @php $extra = (int) $hasGuide + (int) $hasBlog; @endphp
                    {{-- telefonda: ana düğme tam satır, diğer ikisi yan yana; geniş ekranda tek sıra --}}
                    <div @class(['mt-3 grid w-full max-w-xl gap-3', 'grid-cols-2' => $extra > 0, 'sm:grid-cols-[1.6fr_1fr_1fr]' => $extra === 2, 'sm:grid-cols-[1.6fr_1fr]' => $extra === 1])>
                        <a href="#activities-list"
                            class="group col-span-2 inline-flex sm:col-span-1 min-h-[56px] items-center justify-center gap-2.5 rounded-2xl bg-[#C62E2E] px-5 text-[15px] font-bold text-white shadow-[0_14px_30px_-14px_rgba(198,46,46,1)] transition hover:bg-[#A82424]">
                            {{ __('activities-page.city_see') }}
                            <svg class="hero-arrow-icon size-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M6 13l6 6 6-6"/></svg>
                        </a>
                        @if ($hasGuide)
                            <a href="{{ route('cities.show', $city->slug) }}"
                                @class(['inline-flex min-h-[56px] items-center justify-center gap-2 rounded-2xl bg-white/10 px-4 text-sm font-bold text-white ring-1 ring-white/25 backdrop-blur-md transition hover:bg-white hover:text-slate-900', 'col-span-2 sm:col-span-1' => $extra === 1])>
                                <svg class="size-[18px] shrink-0 opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 4.5 3.5 6.6v13l5.5-2.1 6 2.1 5.5-2.1v-13L15 6.6zM9 4.5v13M15 6.6v13"/></svg>
                                <span class="truncate">{{ __('activities-page.hero_guide') }}</span>
                            </a>
                        @endif
                        @if ($hasBlog)
                            <a href="{{ route('blog.index', ['city_id' => $city->id]) }}"
                                @class(['inline-flex min-h-[56px] items-center justify-center gap-2 rounded-2xl bg-white/10 px-4 text-sm font-bold text-white ring-1 ring-white/25 backdrop-blur-md transition hover:bg-white hover:text-slate-900', 'col-span-2 sm:col-span-1' => $extra === 1])>
                                <svg class="size-[18px] shrink-0 opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 7c-1.6-1.7-4.3-2.5-8-2.5v13c3.7 0 6.4.8 8 2.5 1.6-1.7 4.3-2.5 8-2.5v-13c-3.7 0-6.4.8-8 2.5zM12 7v13"/></svg>
                                <span class="truncate">{{ __('activities-page.hero_blog') }}</span>
                            </a>
                        @endif
                    </div>
                @endif
                {{-- bizi takip et: seçici ve düğmelerle aynı blokta, aynı genişlikte ve aynı cam zeminde --}}
                <x-social-follow full class="mt-3 max-w-xl bg-white/10 ring-white/25 backdrop-blur-md" />
            </div>
        </div>
    </section>

    {{-- yapısal veri: aktivite listesi (seçili şehrin aktiviteleri) --}}
    @jsonld(\App\Support\Schema::webPage('CollectionPage', __('activities-page.meta_title'), __('activities-page.meta_description'), [
        'about' => $city ? ['@type' => 'City', 'name' => $city->name, 'url' => route('cities.show', ['slug' => $city->slug])] : null,
        'mainEntity' => $activities->isNotEmpty() ? \App\Support\Schema::itemList($activities->take(50)->map(fn ($a) => [$a->name, route('activities.show', $a->localSlug())])) : null,
    ]))

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
                // Aradığını bulamadın mı kartı: sekmesi seçilmişse yalnızca o sekmede; genel kart her sekmede,
                // ama o sekmeye aynı partnerin özel kartı varsa onun yerine özel kart çıkar
                extraShown(el) {
                    const own = el.dataset.extraType || '';
                    if (own) return own === this.type;
                    return ![...this.$refs.grid.children].some(o => 'extra' in o.dataset && o.dataset.extraType === this.type && o.dataset.partner === el.dataset.partner);
                },
                match(el) {
                    if ('extra' in el.dataset) return this.extraShown(el);
                    const words = this.norm(this.q).split(/\s+/).filter(Boolean);
                    return (this.type === 'all' || el.dataset.type === this.type) && words.every(w => el.dataset.search.includes(w));
                },
                get shown() { return [...this.$refs.grid.children].filter(el => !('extra' in el.dataset) && this.match(el)).length },
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
                        {{-- sekmeler: telefonda eşit genişlikte bölmeli kutu (ikon üstte, yazı altta; taşmaz), büyük ekranda yan yana hap --}}
                        @php $tabs = array_merge(['all' => $activities->count()], $typeCounts->all()); @endphp
                        <div @class(['grid shrink-0 gap-1 rounded-2xl bg-slate-100/80 p-1 lg:flex lg:bg-transparent lg:p-0',
                            [1 => 'grid-cols-1', 2 => 'grid-cols-2', 3 => 'grid-cols-3', 4 => 'grid-cols-4'][count($tabs)] ?? 'grid-cols-4'])>
                            @foreach ($tabs as $t => $n)
                                <button type="button" @click="pick('{{ $t }}')"
                                    :class="tab === '{{ $t }}' ? 'bg-[#C62E2E] text-white shadow-[0_10px_24px_-10px_rgba(198,46,46,0.7)]' : 'text-slate-600 hover:bg-white hover:text-slate-900 lg:hover:bg-slate-50'"
                                    class="flex min-w-0 flex-col items-center justify-center gap-1 rounded-xl px-1.5 py-2.5 text-xs font-semibold transition lg:flex-row lg:gap-2 lg:rounded-2xl lg:px-4 lg:py-3 lg:text-sm">
                                    <span class="relative">
                                        <svg class="size-5 lg:size-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">{!! $tabIcons[$t] ?? $tabIcons['product'] !!}</svg>
                                    </span>
                                    <span class="max-w-full truncate">{{ $t === 'all' ? __('activities-page.all') : $typeLabel($t) }}</span>
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
                    {{-- sonuç sayısı gösterilmiyor (kullanıcı isteği: "22 aktivite" gibi sayılar yok); sadece yükleniyor noktaları --}}
                    <p class="mt-5 flex h-5 items-center text-sm text-slate-500">
                        <span x-show="loading" x-cloak class="inline-flex gap-1" aria-hidden="true">
                            <span class="acts-dot size-1.5 rounded-full bg-[#C62E2E]"></span><span class="acts-dot size-1.5 rounded-full bg-[#C62E2E] [animation-delay:.15s]"></span><span class="acts-dot size-1.5 rounded-full bg-[#C62E2E] [animation-delay:.3s]"></span>
                        </span>
                    </p>

                    {{-- İSKELET KARTLAR (filtre değişirken) --}}
                    <ul x-show="loading" x-cloak aria-hidden="true" class="mt-4 grid grid-cols-2 gap-x-3 gap-y-6 sm:gap-5 lg:grid-cols-3 xl:grid-cols-4">
                        @for ($i = 0; $i < min(8, max(4, $activities->count())); $i++)
                            <li class="overflow-hidden rounded-2xl sm:rounded-3xl sm:bg-white sm:ring-1 sm:ring-slate-200/80">
                                <div class="acts-skeleton aspect-[4/3] rounded-2xl sm:rounded-none"></div>
                                <div class="space-y-3 py-3 sm:p-5">
                                    <div class="flex gap-1.5"><div class="acts-skeleton h-5 w-16 rounded-md"></div><div class="acts-skeleton h-5 w-20 rounded-md"></div></div>
                                    <div class="acts-skeleton h-4 w-11/12 rounded"></div>
                                    <div class="acts-skeleton h-4 w-2/3 rounded"></div>
                                    <div class="flex items-center justify-between border-t border-slate-100 pt-4"><div class="acts-skeleton h-4 w-16 rounded"></div><div class="acts-skeleton size-8 rounded-full"></div></div>
                                </div>
                            </li>
                        @endfor
                    </ul>

                    {{-- KARTLAR: telefonda blog listesi gibi iki sütun (resim + başlık), tablet ve üstünde beyaz kart --}}
                    <ul x-ref="grid" x-show="!loading" class="mt-4 grid grid-cols-2 gap-x-3 gap-y-6 sm:gap-5 lg:grid-cols-3 xl:grid-cols-4">
                        @foreach ($activities as $activity)
                            <li x-show="match($el)" data-type="{{ $activity->type }}"
                                data-search="{{ \Illuminate\Support\Str::lower(\Illuminate\Support\Str::ascii(implode(' ', array_filter((array) $activity->getTranslations('name'))))) }} {{ mb_strtolower($activity->name) }}"
                                x-init="$el.dataset.search = norm($el.dataset.search)">
                                <a href="{{ route('activities.show', $activity->localSlug()) }}" class="group block h-full">
                                    {{-- telefon: blog kartı gibi (resim üstte, rozetler resmin üstünde, altında başlık) --}}
                                    <div class="sm:hidden">
                                        <div class="relative aspect-[4/3] overflow-hidden rounded-2xl bg-slate-200 ring-1 ring-slate-900/5">
                                            @if ($activity->cover)
                                                <x-cover-fit :src="$activity->cover->url('sm')" :alt="$activity->name" loading="lazy" decoding="async" class="absolute inset-0 h-full w-full" />
                                            @endif
                                            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/55 via-transparent to-transparent"></div>
                                            <span @class(['absolute left-2 top-2 max-w-[85%] truncate rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide shadow-sm rtl:left-auto rtl:right-2',
                                                'bg-[#C62E2E] text-white' => $activity->type === 'pass', 'bg-white/90 text-slate-900 backdrop-blur' => $activity->type !== 'pass'])>{{ $badge($activity->type) }}</span>
                                            @if ($activity->audio_guide)
                                                <span class="absolute bottom-2 left-2 inline-flex items-center gap-1 text-[10px] font-semibold text-white/90 rtl:left-auto rtl:right-2">
                                                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 15v-3a8 8 0 0 1 16 0v3"/><path d="M4 15a2 2 0 0 1 2-2h1v7H6a2 2 0 0 1-2-2zM20 15a2 2 0 0 0-2-2h-1v7h1a2 2 0 0 0 2-2z"/></svg>
                                                    {{ __('activities-page.audio') }}
                                                </span>
                                            @endif
                                        </div>
                                        <p class="mt-2.5 text-xs font-medium text-slate-400">{{ $activity->city?->name }}</p>
                                        <h3 class="mt-0.5 line-clamp-3 text-sm font-bold leading-snug text-slate-900">{{ $activity->name }}</h3>
                                    </div>

                                    {{-- tablet ve üstü: beyaz kart --}}
                                    <div class="hidden h-full flex-col overflow-hidden rounded-3xl bg-white ring-1 ring-slate-200/80 transition duration-500 ease-[cubic-bezier(0.22,1,0.36,1)] sm:flex
                                                shadow-[0_1px_2px_rgba(15,23,42,0.04),0_14px_36px_-24px_rgba(15,23,42,0.3)] group-hover:-translate-y-1.5 group-hover:shadow-[0_28px_60px_-28px_rgba(15,23,42,0.45)]">
                                        <div class="relative aspect-[4/3] overflow-hidden bg-slate-100">
                                            @if ($activity->cover)
                                                <x-cover-fit :src="$activity->cover->url('sm')" :alt="$activity->name" loading="lazy" decoding="async"
                                                    class="absolute inset-0 h-full w-full transition duration-700 ease-[cubic-bezier(0.22,1,0.36,1)] group-hover:scale-[1.06]" />
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
                                    </div>
                                </a>
                            </li>
                        @endforeach

                        {{-- ARADIĞINI BULAMADIN MI?: partnerin şehir sayfasına doğrudan bağlantı (detay sayfası yok; panel: Aktiviteler › Partner bağlantıları) --}}
                        @foreach ($moreLinks as $more)
                            @php
                                $partnerName = $more->partner->name;
                                $moreText = $more->count_label
                                    ? __('activities-page.more_text_count', ['city' => $city?->name, 'count' => $more->count_label])
                                    : __('activities-page.more_text', ['city' => $city?->name]);
                            @endphp
                            <li data-extra data-extra-type="{{ $more->type }}" data-partner="{{ $more->partner_id }}" x-show="match($el)" class="h-full">
                                <a href="{{ $more->targetUrl() }}" target="_blank" rel="nofollow sponsored noopener"
                                    class="group relative isolate flex h-full min-h-[15rem] flex-col overflow-hidden rounded-2xl bg-gradient-to-br from-slate-900 via-[#1f2a4d] to-[#C62E2E] p-4 text-white shadow-[0_18px_40px_-22px_rgba(198,46,46,0.8)] ring-1 ring-white/10 transition duration-500 hover:-translate-y-1.5 sm:min-h-[22rem] sm:rounded-3xl sm:p-6">
                                    @if ($moreImage = $more->imageUrl('sm'))
                                        {{-- panelden yüklenen resim arka planda, üstünde koyu geçiş (yazılar okunsun) --}}
                                        <img src="{{ $moreImage }}" alt="" loading="lazy" decoding="async" class="absolute inset-0 -z-20 h-full w-full object-cover transition duration-700 group-hover:scale-105" style="object-position: {{ $more->image_focus ?? 50 }}% center">
                                        <span class="pointer-events-none absolute inset-0 -z-10 bg-gradient-to-t from-slate-950/95 via-slate-950/60 to-slate-950/20" aria-hidden="true"></span>
                                    @else
                                        <span class="pointer-events-none absolute -right-12 -top-12 -z-10 size-44 rounded-full bg-white/10 blur-2xl transition duration-500 group-hover:scale-125" aria-hidden="true"></span>
                                        <span class="pointer-events-none absolute -bottom-16 -left-10 -z-10 size-44 rounded-full bg-amber-400/20 blur-3xl" aria-hidden="true"></span>
                                    @endif

                                    <span class="flex items-start justify-between gap-2">
                                        {{-- partner logosu (yoksa adı) --}}
                                        <span class="inline-flex h-9 max-w-[75%] items-center rounded-xl bg-white px-2.5 shadow sm:h-11 sm:px-3">
                                            @if ($logo = $more->partner->logoUrl())
                                                <img src="{{ $logo }}" alt="{{ $partnerName }}" loading="lazy" class="h-5 w-auto max-w-full object-contain sm:h-6">
                                            @else
                                                <span class="truncate text-xs font-extrabold text-slate-900 sm:text-sm">{{ $partnerName }}</span>
                                            @endif
                                        </span>
                                        <span class="grid size-8 shrink-0 place-items-center rounded-full bg-white/15 transition group-hover:bg-white group-hover:text-slate-900 sm:size-9">
                                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M7 17 17 7M9 7h8v8"/></svg>
                                        </span>
                                    </span>

                                    <span class="mt-auto block pt-5">
                                        <span class="text-[10px] font-bold uppercase tracking-[0.18em] text-amber-300 sm:text-[11px]">{{ __('activities-page.more_eyebrow') }}</span>
                                        <span class="mt-1.5 block text-base font-extrabold leading-tight sm:text-2xl">{{ __('activities-page.more_title') }}</span>
                                        <span class="mt-1.5 block text-xs leading-snug text-white/75 sm:text-sm">{{ $moreText }}</span>
                                    </span>

                                    <span class="mt-4 inline-flex items-center justify-center gap-1.5 rounded-xl bg-white px-3 py-2.5 text-xs font-bold text-slate-900 transition group-hover:bg-amber-300 sm:mt-5 sm:py-3 sm:text-sm">
                                        <span class="truncate">{{ __('activities-page.more_cta', ['partner' => $partnerName]) }}</span>
                                        <svg class="size-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M14 4h6v6M20 4l-9 9M18 14v4a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4"/></svg>
                                    </span>
                                    <span class="mt-2 hidden text-center text-[11px] text-white/50 sm:block">{{ __('activities-page.more_note') }}</span>
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
