@extends('layouts.app')

@section('title', __('campaigns-page.meta_title'))
@section('meta_description', __('campaigns-page.meta_description'))

{{--
    Kampanyalar: lacivert üst bölüm (sitenin kırmızısı + altın), sağda tavşan maskot; sayfa açılınca havai fişek.
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

        {{-- ÜST: lacivert, kırmızı + altın ışık, sağda tavşan --}}
        <section class="relative isolate overflow-hidden bg-slate-950 text-white">
            <div class="pointer-events-none absolute inset-0 -z-10" aria-hidden="true">
                <div class="absolute inset-0 opacity-[0.08] [background-image:radial-gradient(rgba(255,255,255,.9)_1px,transparent_1px)] [background-size:26px_26px]"></div>
                <div class="absolute -left-40 -top-40 size-[36rem] rounded-full bg-[#C62E2E]/35 blur-[140px]"></div>
                <div class="absolute -bottom-48 right-[10%] size-[34rem] rounded-full bg-amber-400/20 blur-[140px]"></div>
            </div>

            <div class="mx-auto max-w-7xl px-4 pb-14 pt-6 md:pb-16 md:pt-8">
                <x-breadcrumbs dark :items="[
                    ['label' => __('activity-show.home'), 'url' => route('home')],
                    ['label' => __('navbar.campaigns'), 'url' => route('campaigns')],
                ]" />

                <div class="mt-8 grid items-center gap-8 lg:mt-10 lg:grid-cols-12">
                    <div class="relative lg:col-span-7">
                        {{-- telefon/tablet: tavşan büyük, sağ altta (sarı "aktif fırsat" düğmesiyle karşılıklı), yazıların arkasında; masaüstünde sağ sütunda --}}
                        <img src="{{ asset('images/kosantavsan-campaign.webp') }}" alt="" aria-hidden="true" width="880" height="810"
                            class="campaign-float pointer-events-none absolute bottom-0 -right-2 -z-10 w-60 max-w-none opacity-60 mix-blend-screen sm:right-0 sm:w-72 lg:hidden">
                        <span class="inline-flex items-center gap-2 rounded-full bg-amber-400/15 px-3.5 py-1.5 text-xs font-bold uppercase tracking-[0.16em] text-amber-300 ring-1 ring-amber-300/30">
                            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">{!! $gift !!}</svg>
                            {{ $hero['eyebrow'] }}
                        </span>
                        <h1 class="mt-5 text-[40px] font-extrabold leading-[1.02] tracking-[-0.04em] sm:text-6xl">{{ $hero['title'] }}</h1>
                        <p class="mt-5 max-w-xl text-base leading-relaxed text-white/70 md:text-lg">{{ $hero['description'] }}</p>
                        <p class="mt-6 inline-flex items-center gap-2 rounded-full bg-amber-400 px-4 py-2 text-sm font-extrabold text-slate-950 shadow-[0_12px_30px_-10px_rgba(251,191,36,0.7)]">
                            <span class="relative flex size-2"><span class="absolute inline-flex size-full animate-ping rounded-full bg-[#C62E2E] opacity-70"></span><span class="relative inline-flex size-2 rounded-full bg-[#C62E2E]"></span></span>
                            {{ trans_choice('campaigns-page.count', $campaigns->count(), ['count' => $campaigns->count()]) }}
                        </p>
                    </div>

                    {{-- koşan tavşan (public/images/kosantavsan-campaign.webp = kosantavsanlogo.webp'nin gölgesiz kırpılmışı): siyah zemini mix-blend-screen ile lacivertte kaybolur --}}
                    <div class="relative mx-auto hidden w-full lg:col-span-5 lg:block" aria-hidden="true">
                        <div class="absolute inset-x-10 inset-y-6 rounded-full bg-gradient-to-br from-amber-300/25 via-[#C62E2E]/20 to-transparent blur-3xl"></div>
                        <img src="{{ asset('images/kosantavsan-campaign.webp') }}" alt="" width="880" height="810" class="campaign-float relative mx-auto w-full max-w-xs mix-blend-screen">
                        <span class="absolute left-[12%] top-[8%] text-2xl text-amber-300 campaign-twinkle">✦</span>
                        <span class="absolute right-[10%] top-[4%] text-lg text-white/70 campaign-twinkle [animation-delay:.8s]">✦</span>
                        <span class="absolute bottom-[14%] right-[6%] text-xl text-[#FF8A8C] campaign-twinkle [animation-delay:1.4s]">✦</span>
                    </div>
                </div>

                {{-- nasıl kullanılır --}}
                <ol class="mt-10 grid gap-3 sm:grid-cols-3">
                    @foreach (['step1', 'step2', 'step3'] as $step)
                        <li class="flex items-center gap-3 rounded-2xl bg-white/[0.06] px-4 py-3.5 ring-1 ring-white/10">
                            <span class="grid size-8 shrink-0 place-items-center rounded-full bg-amber-400 text-sm font-extrabold text-slate-950">{{ $loop->iteration }}</span>
                            <span class="text-sm font-semibold text-white/90">{{ __('campaigns-page.' . $step) }}</span>
                        </li>
                    @endforeach
                </ol>
            </div>
        </section>

        {{-- KAMPANYALAR: liste --}}
        <section class="bg-slate-50 py-12 md:py-16">
            <div class="mx-auto max-w-5xl px-4">
                <ul class="space-y-5">
                    @foreach ($campaigns as $campaign)
                        @php
                            $partner = $campaign->partner;
                            $logo = $partner?->logoUrl();
                            $daysLeft = $campaign->ends_at ? (int) now()->startOfDay()->diffInDays($campaign->ends_at->startOfDay(), false) : null;
                            $link = $campaign->link();
                        @endphp
                        <li class="group relative overflow-hidden rounded-[28px] bg-white shadow-[0_20px_50px_-36px_rgba(15,23,42,0.45)] ring-1 ring-slate-200/80 transition duration-300 hover:shadow-[0_26px_60px_-34px_rgba(198,46,46,0.45)] hover:ring-[#C62E2E]/25">
                            <span class="absolute inset-y-0 left-0 w-1.5 bg-gradient-to-b from-[#C62E2E] to-amber-400 rtl:left-auto rtl:right-0" aria-hidden="true"></span>
                            <div @class(['grid gap-6 p-5 sm:p-7 md:items-center', 'md:grid-cols-[auto_1fr_17rem]' => $logo, 'md:grid-cols-[1fr_17rem]' => !$logo])>
                                {{-- logo: sadece partnerin logosu yüklüyse --}}
                                @if ($logo)
                                    <span class="grid size-20 shrink-0 place-items-center rounded-2xl bg-white p-2.5 shadow-sm ring-1 ring-slate-200">
                                        <img src="{{ $logo }}" alt="{{ $partner->name }}" loading="lazy" class="max-h-full max-w-full object-contain">
                                    </span>
                                @endif

                                {{-- bilgi --}}
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        @if ($partner)
                                            <span class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400">{{ $partner->name }}</span>
                                        @endif
                                        @if ($campaign->badge)
                                            <span class="rounded-lg bg-amber-400 px-2.5 py-1 text-sm font-extrabold leading-none text-slate-950">{{ $campaign->badge }}</span>
                                        @endif
                                    </div>
                                    <h2 class="mt-1.5 text-xl font-extrabold leading-snug tracking-tight text-slate-900 sm:text-2xl">{{ $campaign->title }}</h2>
                                    @if ($campaign->description)
                                        <p class="mt-1.5 text-sm leading-relaxed text-slate-600">{{ $campaign->description }}</p>
                                    @endif
                                    @if ($campaign->note)
                                        <p class="mt-3 flex items-start gap-2 rounded-xl bg-amber-50 px-3 py-2 text-sm font-medium text-amber-900 ring-1 ring-amber-200/70">
                                            <svg class="mt-0.5 size-4 shrink-0 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><circle cx="12" cy="12" r="9"/><path d="M12 11v5M12 8h.01"/></svg>
                                            {{ $campaign->note }}
                                        </p>
                                    @endif
                                    <p class="mt-3 inline-flex items-center gap-1.5 text-xs font-semibold {{ $daysLeft !== null && $daysLeft <= 7 ? 'text-[#C62E2E]' : 'text-slate-500' }}">
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

                                {{-- kod + düğme --}}
                                <div class="space-y-2.5">
                                    @if ($campaign->code)
                                        <button type="button" @click="copy(@js($campaign->code))" title="{{ __('campaigns-page.copy') }}"
                                            class="flex w-full items-stretch overflow-hidden rounded-2xl border-2 border-dashed border-slate-300 bg-slate-50 text-left transition hover:border-[#C62E2E] rtl:text-right">
                                            <span class="min-w-0 flex-1 px-4 py-2.5">
                                                <span class="block text-[10px] font-bold uppercase tracking-[0.16em] text-slate-400">{{ __('campaigns-page.code_label') }}</span>
                                                <span class="block truncate font-mono text-lg font-extrabold tracking-[0.12em] text-slate-900">{{ $campaign->code }}</span>
                                            </span>
                                            <span class="grid shrink-0 place-items-center bg-white px-3.5 text-slate-500 transition group-hover:text-[#C62E2E]">
                                                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><rect x="8" y="8" width="12" height="12" rx="2.5"/><path d="M16 8V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h2"/></svg>
                                            </span>
                                        </button>
                                    @endif
                                    @if ($link)
                                        <a href="{{ $link }}" @if ($campaign->isExternal()) target="_blank" rel="sponsored noopener" @endif
                                            @if ($campaign->code) @click="copy(@js($campaign->code))" @endif
                                            class="flex w-full items-center justify-center gap-2 rounded-2xl bg-[#C62E2E] px-5 py-3.5 text-sm font-bold text-white shadow-[0_12px_26px_-12px_rgba(198,46,46,0.9)] transition hover:bg-[#A82424]">
                                            {{ $campaign->code ? __('campaigns-page.copy_and_go') : __('campaigns-page.go') }}
                                            <svg class="size-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>

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
            if (!canvas || matchMedia('(prefers-reduced-motion: reduce)').matches) { canvas?.remove(); return; }
            const ctx = canvas.getContext('2d');
            const dpr = Math.min(window.devicePixelRatio || 1, 2);
            const size = () => { canvas.width = innerWidth * dpr; canvas.height = innerHeight * dpr; ctx.setTransform(dpr, 0, 0, dpr, 0, 0); };
            size(); addEventListener('resize', size);
            const colors = ['#C62E2E', '#FF6B6E', '#FBBF24', '#FDE68A', '#FFFFFF', '#F59E0B'];
            const sparks = [];
            const burst = (x, y) => {
                const color = colors[Math.floor(Math.random() * colors.length)];
                const count = 70 + Math.floor(Math.random() * 30);
                for (let i = 0; i < count; i++) {
                    const angle = (Math.PI * 2 * i) / count + Math.random() * 0.2;
                    const speed = 2.2 + Math.random() * 4.2;
                    sparks.push({ x, y, vx: Math.cos(angle) * speed, vy: Math.sin(angle) * speed, life: 1, decay: 0.010 + Math.random() * 0.012,
                        color: Math.random() < 0.25 ? colors[Math.floor(Math.random() * colors.length)] : color, r: 1.6 + Math.random() * 1.8 });
                }
            };
            const shots = [[0.25, 0.28, 0], [0.72, 0.22, 350], [0.5, 0.36, 700], [0.15, 0.45, 1050], [0.85, 0.42, 1300], [0.4, 0.2, 1650], [0.62, 0.48, 1950]];
            shots.forEach(([x, y, delay]) => setTimeout(() => burst(innerWidth * x, innerHeight * y), delay + 250));
            const started = performance.now();
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
            requestAnimationFrame(frame);
        })();
    </script>
@endsection
