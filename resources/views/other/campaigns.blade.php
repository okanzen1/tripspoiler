@extends('layouts.app')

@section('title', __('campaigns-page.meta_title'))
@section('meta_description', __('campaigns-page.meta_description'))

{{--
    Kampanyalar: lacivert üst bölüm (sitenin kırmızısı + altın), sağda indirim biletleri ve önünde tavşan maskot; altında 3 adım; sayfa açılınca havai fişek.
    Altında kampanyalar liste halinde: partner logosu, etiket, başlık, açıklama, not, süre, kod + Kopyala, Kampanyaya git.
    Kampanyalar panelden (Kampanyalar); üst yazılar Sayfalar › Kampanyalar › Hero alanı.
    Yayında kampanya yoksa bu sayfa anasayfaya yönlenir (CampaignController).
--}}
@php
    $locale = app()->getLocale();
    $hero = \App\Support\PageHero::texts('campaigns', [
        'eyebrow' => __('campaigns-page.eyebrow'), 'title' => __('campaigns-page.title'), 'description' => __('campaigns-page.desc'),
    ]);
    $gift = '<path d="M20 12v8a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-8M2.5 7.5h19v4.5h-19zM12 21V7.5M12 7.5H8a2.5 2.5 0 1 1 0-5c2.5 0 4 5 4 5zM12 7.5h4a2.5 2.5 0 1 0 0-5c-2.5 0-4 5-4 5z"/>';
@endphp

@section('content')
    <div x-data="{
            toast: '',
            timer: null,
            // Kod panoya kopyalanır; tıklama anında başlar ki bağlantı yeni sekmede açılsa da kopyalama tamamlansın
            copy(code) {
                const fallback = () => {
                    const t = document.createElement('textarea'); t.value = code; document.body.appendChild(t); t.select();
                    try { document.execCommand('copy') } catch (e) {} t.remove();
                };
                try { navigator.clipboard.writeText(code).catch(fallback) } catch (e) { fallback() }
                this.toast = @js(__('campaigns-page.copied'));
                clearTimeout(this.timer); this.timer = setTimeout(() => this.toast = '', 2200);
            },
        }">

        {{-- ÜST: lacivert, kırmızı + altın ışık; sağda iki indirim bileti ve önünde koşan tavşan; altında 3 adım --}}
        @php
            $stepIcons = [
                'step1' => '<rect x="8" y="8" width="12" height="12" rx="2.5"/><path d="M16 8V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h2"/>',
                'step2' => '<path d="M14 4h6v6M20 4l-9 9M18 14v4a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4"/>',
                'step3' => '<path d="M3 4h2l2.4 11.2a2 2 0 0 0 2 1.6h7.7a2 2 0 0 0 2-1.5L21 8H6.2"/><circle cx="10" cy="20" r="1.3"/><circle cx="17" cy="20" r="1.3"/>',
            ];
        @endphp
        <section class="relative isolate overflow-hidden bg-slate-950 text-white">
            <div class="pointer-events-none absolute inset-0 -z-10" aria-hidden="true">
                <div class="absolute inset-0 opacity-[0.08] [background-image:radial-gradient(rgba(255,255,255,.9)_1px,transparent_1px)] [background-size:26px_26px]"></div>
                <div class="absolute -left-40 -top-40 size-[44rem] rounded-full bg-[radial-gradient(circle,rgba(198,46,46,0.38),transparent_65%)]"></div>
                <div class="absolute -bottom-56 right-[5%] size-[46rem] rounded-full bg-[radial-gradient(circle,rgba(251,191,36,0.22),transparent_65%)]"></div>
                {{-- konfeti --}}
                <span class="absolute left-[46%] top-[18%] size-2 rotate-45 rounded-sm bg-amber-300/70"></span>
                <span class="absolute left-[54%] top-[64%] size-1.5 rounded-full bg-[#FF6B6E]/80"></span>
                <span class="absolute right-[6%] top-[22%] h-1 w-4 rotate-[30deg] rounded-full bg-white/40"></span>
                <span class="absolute bottom-[30%] left-[40%] h-1 w-3 -rotate-12 rounded-full bg-amber-300/50"></span>
            </div>

            <div class="mx-auto max-w-7xl px-4 pb-14 pt-6 md:pb-20 md:pt-8">
                <x-breadcrumbs dark :items="[
                    ['label' => __('activity-show.home'), 'url' => route('home')],
                    ['label' => __('navbar.campaigns'), 'url' => route('campaigns')],
                ]" />

                <div class="mt-8 grid items-center gap-10 lg:mt-12 lg:grid-cols-12">
                    <div class="relative lg:col-span-7">
                        {{-- telefon/tablet: tavşan sağ altta, yazıların arkasında; masaüstünde sağ sütunda biletlerle --}}
                        <img src="{{ asset('images/kosantavsan-campaign-alpha.webp') }}" alt="" aria-hidden="true" width="640" height="589" decoding="async"
                            class="campaign-float pointer-events-none absolute -bottom-6 -right-4 -z-10 w-44 max-w-none opacity-25 sm:right-0 sm:w-64 sm:opacity-40 lg:hidden">
                        <span class="inline-flex items-center gap-2 rounded-full bg-amber-400/15 px-3.5 py-1.5 text-xs font-bold uppercase tracking-[0.16em] text-amber-300 ring-1 ring-amber-300/30">
                            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">{!! $gift !!}</svg>
                            {{ $hero['eyebrow'] }}
                        </span>
                        <h1 class="mt-5 text-[42px] font-extrabold leading-[0.98] tracking-[-0.04em] sm:text-6xl lg:text-7xl">{{ $hero['title'] }}</h1>
                        <p class="mt-6 max-w-xl text-base leading-relaxed text-white/70 md:text-lg">{{ $hero['description'] }}</p>
                        <div class="mt-8 flex flex-wrap items-center gap-3">
                            <p class="inline-flex items-center gap-2 rounded-full bg-amber-400 px-4 py-2.5 text-sm font-extrabold text-slate-950 shadow-[0_12px_30px_-10px_rgba(251,191,36,0.7)]">
                                <span class="relative flex size-2"><span class="absolute inline-flex size-full animate-ping rounded-full bg-[#C62E2E] opacity-70"></span><span class="relative inline-flex size-2 rounded-full bg-[#C62E2E]"></span></span>
                                {{ trans_choice('campaigns-page.count', $campaigns->count(), ['count' => $campaigns->count()]) }}
                            </p>
                            <p class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-2.5 text-sm font-bold text-white ring-1 ring-white/15">
                                <svg class="size-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12.5 4.5 4.5L19 7.5"/></svg>
                                {{ __('campaigns-page.free') }}
                            </p>
                        </div>
                        <x-social-follow class="mt-5" />
                    </div>

                    {{-- biletler + koşan tavşan (public/images/kosantavsan-campaign-alpha.webp, arka planı şeffaf) --}}
                    <div class="relative mx-auto hidden h-[400px] w-full max-w-md lg:col-span-5 lg:block" aria-hidden="true">
                        <div class="absolute inset-0 rounded-full bg-[radial-gradient(circle,rgba(251,191,36,0.22),transparent_62%)]"></div>
                        {{-- arkadaki kırmızı bilet --}}
                        <div class="absolute left-[2%] top-[6%] flex h-40 w-72 rotate-[9deg] overflow-hidden rounded-[24px] bg-gradient-to-br from-[#FF6B6E] to-[#A82424] shadow-[0_30px_60px_-25px_rgba(198,46,46,0.8)]">
                            <div class="flex flex-1 items-center justify-center text-7xl font-black text-white/25">%</div>
                            <div class="relative w-16 border-l-2 border-dashed border-white/30">
                                <span class="absolute -left-3 -top-3 size-6 rounded-full bg-slate-950"></span>
                                <span class="absolute -bottom-3 -left-3 size-6 rounded-full bg-slate-950"></span>
                            </div>
                        </div>
                        {{-- öndeki altın bilet --}}
                        <div class="absolute left-[8%] top-[24%] flex h-44 w-80 -rotate-[7deg] overflow-hidden rounded-[26px] bg-gradient-to-br from-amber-200 via-amber-400 to-orange-500 text-slate-950 shadow-[0_35px_70px_-25px_rgba(251,191,36,0.75)]">
                            <div class="flex flex-1 flex-col justify-center px-6">
                                <span class="text-[10px] font-extrabold uppercase tracking-[0.22em] text-slate-950/60">{{ __('campaigns-page.code_label') }}</span>
                                <span class="mt-1 text-6xl font-black leading-none tracking-tighter">%</span>
                                <span class="mt-3 inline-flex w-max items-center gap-1.5 rounded-lg border-2 border-dashed border-slate-950/30 px-2.5 py-1 font-mono text-sm font-bold tracking-[0.2em]">TRIP•••</span>
                            </div>
                            <div class="relative flex w-16 items-center justify-center border-l-2 border-dashed border-slate-950/25">
                                <span class="absolute -left-3 -top-3 size-6 rounded-full bg-slate-950"></span>
                                <span class="absolute -bottom-3 -left-3 size-6 rounded-full bg-slate-950"></span>
                                <svg class="size-7 text-slate-950/70" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $gift !!}</svg>
                            </div>
                        </div>
                        <img src="{{ asset('images/kosantavsan-campaign-alpha.webp') }}" alt="" width="640" height="589" decoding="async"
                            class="campaign-float absolute -bottom-2 -right-6 w-64 drop-shadow-[0_25px_30px_rgba(0,0,0,0.45)]">
                        <span class="campaign-twinkle absolute left-[0%] top-[2%] text-2xl text-amber-300">✦</span>
                        <span class="campaign-twinkle absolute right-[4%] top-[0%] text-lg text-white/70 [animation-delay:.8s]">✦</span>
                        <span class="campaign-twinkle absolute bottom-[10%] left-[6%] text-xl text-[#FF8A8C] [animation-delay:1.4s]">✦</span>
                    </div>
                </div>

                {{-- nasıl kullanılır: 3 adım, aralarında ok --}}
                <ol class="mt-12 grid gap-3 sm:grid-cols-3 sm:gap-5">
                    @foreach (['step1', 'step2', 'step3'] as $step)
                        <li class="relative flex items-center gap-4 rounded-2xl bg-white/[0.06] p-4 ring-1 ring-white/10 backdrop-blur-sm sm:p-5">
                            <span class="relative grid size-12 shrink-0 place-items-center rounded-xl bg-gradient-to-br from-amber-300 to-amber-500 text-slate-950 shadow-[0_10px_24px_-10px_rgba(251,191,36,0.8)]">
                                <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $stepIcons[$step] !!}</svg>
                                <span class="absolute -right-1.5 -top-1.5 grid size-5 place-items-center rounded-full bg-[#C62E2E] text-[10px] font-extrabold text-white ring-2 ring-slate-950">{{ $loop->iteration }}</span>
                            </span>
                            <span class="min-w-0">
                                <span class="block text-[10px] font-bold uppercase tracking-[0.18em] text-amber-300/80">{{ __('campaigns-page.step_label', ['n' => $loop->iteration]) }}</span>
                                <span class="mt-0.5 block font-bold text-white">{{ __('campaigns-page.' . $step) }}</span>
                            </span>
                            @unless ($loop->last)
                                <span class="absolute -right-[18px] top-1/2 z-10 hidden size-8 -translate-y-1/2 place-items-center rounded-full bg-slate-900 text-amber-300 ring-1 ring-white/15 sm:grid rtl:-left-[18px] rtl:right-auto rtl:rotate-180" aria-hidden="true">
                                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                                </span>
                            @endunless
                        </li>
                    @endforeach
                </ol>
            </div>
        </section>

        {{-- KAMPANYALAR: kupon görünümlü liste (banner'daki yazılarla aynı genişlikte: max-w-7xl) --}}
        <section class="bg-slate-50 py-12 md:py-16">
            <div class="mx-auto max-w-7xl px-4">
                <ul class="space-y-6">
                    @foreach ($campaigns as $campaign)
                        @php
                            $partner = $campaign->partner;
                            $logo = $partner?->logoUrl();
                            $daysLeft = $campaign->ends_at ? (int) now()->startOfDay()->diffInDays($campaign->ends_at->startOfDay(), false) : null;
                            $link = $campaign->link();
                            $code = $campaign->code ? \Illuminate\Support\Str::upper($campaign->code) : null;
                        @endphp
                        <li class="group flex flex-col overflow-hidden rounded-[28px] bg-white shadow-[0_24px_60px_-40px_rgba(15,23,42,0.55)] ring-1 ring-slate-200/80 transition duration-300 hover:-translate-y-0.5 hover:shadow-[0_30px_70px_-38px_rgba(198,46,46,0.5)] md:flex-row">
                            {{-- BİLGİ --}}
                            <div class="order-3 min-w-0 flex-1 p-6 sm:p-8 md:order-1">
                                {{-- logo (kutuyu kenarına kadar doldurur, kırpılmaz) --}}
                                <div class="flex items-start">
                                    @if ($logo)
                                        {{-- kutu logonun kendi oranında: yükseklik sabit, genişlik logoya göre; logo asla kırpılmaz --}}
                                        <span class="inline-flex h-16 max-w-[16rem] shrink-0 items-center overflow-hidden rounded-2xl bg-white p-2 ring-1 ring-slate-200 sm:h-[72px]">
                                            <img src="{{ $logo }}" alt="{{ $partner->name }}" loading="lazy" class="h-full w-auto max-w-full object-contain">
                                        </span>
                                    @elseif ($partner)
                                        <span class="text-sm font-extrabold uppercase tracking-[0.14em] text-slate-500">{{ $partner->name }}</span>
                                    @endif
                                </div>

                                <h2 @class(['text-2xl font-extrabold leading-tight tracking-[-0.02em] text-slate-900 sm:text-[28px]', 'mt-5' => $logo || $partner])>{{ $campaign->title }}</h2>
                                @if ($campaign->description)
                                    <p class="mt-3 max-w-2xl text-[15px] leading-relaxed text-slate-600">{{ $campaign->description }}</p>
                                @endif
                                @if ($campaign->note)
                                    <p class="mt-4 flex max-w-2xl items-start gap-2.5 rounded-2xl bg-amber-50 px-4 py-3 text-sm font-medium leading-relaxed text-amber-900 ring-1 ring-amber-200/70">
                                        <svg class="mt-0.5 size-4 shrink-0 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><circle cx="12" cy="12" r="9"/><path d="M12 11v5M12 8h.01"/></svg>
                                        {{ $campaign->note }}
                                    </p>
                                @endif
                                <p class="mt-5 inline-flex items-center gap-1.5 text-sm font-semibold {{ $daysLeft !== null && $daysLeft <= 7 ? 'text-[#C62E2E]' : 'text-slate-500' }}">
                                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"><circle cx="12" cy="12" r="8.5"/><path d="M12 7.5V12l3 2"/></svg>
                                    @if ($daysLeft === null)
                                        {{ __('campaigns-page.no_end') }}
                                    @elseif ($daysLeft <= 7)
                                        {{ $daysLeft === 0 ? __('campaigns-page.last_day') : trans_choice('campaigns-page.days_left', $daysLeft, ['count' => $daysLeft]) }}
                                    @else
                                        {{ __('campaigns-page.ends', ['date' => $campaign->ends_at->locale($locale)->isoFormat('D MMMM YYYY')]) }}
                                    @endif
                                </p>
                            </div>

                            {{-- kupon kesik çizgisi ve iki yandaki oyuklar (telefonda yatay, masaüstünde dikey) --}}
                            <div class="relative order-2 h-0 shrink-0 md:h-auto md:w-0" aria-hidden="true">
                                <span class="absolute -left-3 -top-3 size-6 rounded-full bg-slate-50 md:-top-3 md:left-[-12px]"></span>
                                <span class="absolute -right-3 -top-3 size-6 rounded-full bg-slate-50 md:-bottom-3 md:left-[-12px] md:right-auto md:top-auto"></span>
                            </div>

                            {{-- KOD: koyu kupon ucu --}}
                            <div class="order-1 flex flex-col justify-center gap-3 border-b-2 border-dashed border-slate-300 bg-slate-950 p-6 text-white sm:p-8 md:order-3 md:w-[26rem] md:shrink-0 md:border-b-0 md:border-l-2">
                                @if ($campaign->badge)
                                    <span class="mb-2 w-fit rounded-xl bg-amber-400 px-3.5 py-2 text-sm font-extrabold uppercase leading-none tracking-wide text-slate-950 shadow-[0_8px_20px_-10px_rgba(245,158,11,0.9)]">{{ $campaign->badge }}</span>
                                @endif
                                @if ($code)
                                    <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-white/50">{{ __('campaigns-page.code_label') }}</p>
                                    <button type="button" @click="copy(@js($code))" title="{{ __('campaigns-page.copy') }}"
                                        class="group/code flex w-full items-center justify-between gap-3 rounded-2xl border-2 border-dashed border-amber-300/60 bg-white/[0.06] px-5 py-4 text-left transition hover:border-amber-300 hover:bg-white/10 rtl:text-right">
                                        <span class="min-w-0 break-all text-xl font-black tracking-[0.14em] text-white sm:text-2xl">{{ $code }}</span>
                                        <span class="inline-flex shrink-0 items-center gap-1.5 rounded-xl bg-amber-400 px-3 py-2 text-xs font-extrabold text-slate-950 transition group-hover/code:bg-amber-300">
                                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="8" y="8" width="12" height="12" rx="2.5"/><path d="M16 8V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h2"/></svg>
                                            {{ __('campaigns-page.copy') }}
                                        </span>
                                    </button>
                                @endif
                                @if ($link)
                                    <a href="{{ $link }}" @if ($campaign->isExternal()) target="_blank" rel="sponsored noopener" @endif
                                        @if ($code) @click="copy(@js($code))" @endif
                                        class="flex w-full items-center justify-center gap-2 rounded-2xl bg-[#C62E2E] px-5 py-4 text-[15px] font-bold text-white shadow-[0_14px_30px_-12px_rgba(198,46,46,0.9)] transition hover:bg-[#A82424]">
                                        {{ $code ? __('campaigns-page.copy_and_go') : __('campaigns-page.go') }}
                                        <svg class="size-4 transition group-hover:translate-x-0.5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                                    </a>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
                {{-- koşullar + komisyon açıklaması --}}
                <p class="mx-auto mt-8 flex max-w-3xl items-start justify-center gap-2 text-center text-xs leading-relaxed text-slate-400">
                    <svg class="mt-0.5 size-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><circle cx="12" cy="12" r="9"/><path d="M12 11v5M12 8h.01"/></svg>
                    <span>{{ __('campaigns-page.terms') }}</span>
                </p>
            </div>
        </section>

        {{-- kopyalandı bildirimi --}}
        <div x-show="toast" x-cloak x-transition.opacity.duration.200ms role="status"
            class="fixed bottom-6 left-1/2 z-50 -translate-x-1/2 rounded-full bg-slate-900 px-5 py-3 text-sm font-semibold text-white shadow-2xl">
            <span class="mr-1.5 text-emerald-400">✓</span><span x-text="toast"></span>
        </div>
    </div>

    {{-- yapısal veri: kampanya listesi (her kampanya bir Offer: kod, bağlantı, geçerlilik, partner) --}}
    @jsonld(\App\Support\Schema::webPage('CollectionPage', __('campaigns-page.meta_title'), __('campaigns-page.meta_description'), [
        'mainEntity' => [
            '@type' => 'ItemList',
            'numberOfItems' => $campaigns->count(),
            'itemListElement' => $campaigns->values()->map(fn ($c, $i) => [
                '@type' => 'ListItem',
                'position' => $i + 1,
                'item' => array_filter([
                    '@type' => 'Offer',
                    'name' => $c->title,
                    'description' => $c->description ?: null,
                    'url' => $c->link() ?: route('campaigns'),
                    'category' => $c->badge ?: null,
                    'validFrom' => $c->starts_at?->toDateString(),
                    'validThrough' => $c->ends_at?->toDateString(),
                    'offeredBy' => $c->partner ? array_filter(['@type' => 'Organization', 'name' => $c->partner->name, 'url' => $c->partner->website ?: null]) : \App\Support\Schema::organizationRef(),
                ]),
            ])->all(),
        ],
    ]))

    <x-page-sections page="campaigns" />

    {{-- Havai fişek: sayfa açılınca birkaç patlama (sitenin kırmızısı, altın, beyaz); "hareketi azalt" açıksa yok --}}
    <canvas id="campaign-fireworks" class="pointer-events-none fixed inset-0 z-[60] h-full w-full" aria-hidden="true"></canvas>
    <script>
        (() => {
            const canvas = document.getElementById('campaign-fireworks');
            if (!canvas || matchMedia('(prefers-reduced-motion: reduce)').matches || navigator.connection?.saveData) { canvas?.remove(); return; }
            const small = innerWidth < 768;
            const ctx = canvas.getContext('2d');
            const dpr = small ? 1 : Math.min(window.devicePixelRatio || 1, 2);
            const size = () => { canvas.width = innerWidth * dpr; canvas.height = innerHeight * dpr; ctx.setTransform(dpr, 0, 0, dpr, 0, 0); };
            size(); addEventListener('resize', size);
            const colors = ['#C62E2E', '#FF6B6E', '#FBBF24', '#FDE68A', '#FFFFFF', '#F59E0B'];
            const sparks = [];
            const burst = (x, y) => {
                const color = colors[Math.floor(Math.random() * colors.length)];
                const count = small ? 34 + Math.floor(Math.random() * 12) : 70 + Math.floor(Math.random() * 30);
                for (let i = 0; i < count; i++) {
                    const angle = (Math.PI * 2 * i) / count + Math.random() * 0.2;
                    const speed = 2.2 + Math.random() * 4.2;
                    sparks.push({ x, y, vx: Math.cos(angle) * speed, vy: Math.sin(angle) * speed, life: 1, decay: 0.010 + Math.random() * 0.012,
                        color: Math.random() < 0.25 ? colors[Math.floor(Math.random() * colors.length)] : color, r: 1.6 + Math.random() * 1.8 });
                }
            };
            const shots = small
                ? [[0.3, 0.25, 0], [0.7, 0.32, 400], [0.5, 0.2, 800]]
                : [[0.25, 0.28, 0], [0.72, 0.22, 350], [0.5, 0.36, 700], [0.15, 0.45, 1050], [0.85, 0.42, 1300], [0.4, 0.2, 1650], [0.62, 0.48, 1950]];
            // sayfa (resimler) yüklendikten sonra başlar: açılışı yavaşlatmasın
            const start = () => {
                shots.forEach(([x, y, delay]) => setTimeout(() => burst(innerWidth * x, innerHeight * y), delay + 150));
                requestAnimationFrame(frame);
            };
            let started = performance.now();
            const frame = (now) => {
                ctx.clearRect(0, 0, innerWidth, innerHeight);
                for (let i = sparks.length - 1; i >= 0; i--) {
                    const s = sparks[i];
                    s.vx *= 0.985; s.vy = s.vy * 0.985 + 0.06; s.x += s.vx; s.y += s.vy; s.life -= s.decay;
                    if (s.life <= 0) { sparks.splice(i, 1); continue; }
                    ctx.globalAlpha = Math.max(s.life, 0);
                    ctx.fillStyle = s.color;
                    ctx.beginPath(); ctx.arc(s.x, s.y, s.r, 0, Math.PI * 2); ctx.fill();
                }
                if (sparks.length || now - started < 2600) requestAnimationFrame(frame);
                else { removeEventListener('resize', size); canvas.remove(); }
            };
            const go = () => { started = performance.now(); start(); };
            document.readyState === 'complete' ? go() : addEventListener('load', go, { once: true });
        })();
    </script>
@endsection
