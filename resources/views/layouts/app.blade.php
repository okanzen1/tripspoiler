<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ LaravelLocalization::getCurrentLocaleDirection() }}">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="icon" href="{{ asset('favicon.ico') . '?v=3' }}">

    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') . '?v=3' }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') . '?v=3' }}">

    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') . '?v=3' }}">

    @php
        // @section('title', …) kısa formu içeriği zaten e() ile kaçırır: aşağıda {!! !!} ile basılır (çift kaçış olmasın)
        $currentLocale = app()->getLocale();
        $locales = LaravelLocalization::getSupportedLocales();
        $canonical = \App\Support\Schema::canonical();
        $pageTitle = trim($__env->yieldContent('title', 'TripSpoiler'));
        $pageDescription = trim($__env->yieldContent('meta_description', 'Curated city experiences, museum guides and travel insights from around the world.'));
        // Paylaşım resmi (WhatsApp, Facebook…): sayfa vermediyse o sayfanın panelden yüklenen banner'ı,
        // o da yoksa anasayfa hero resmi (PageBanner::urls zinciri), en son varsayılan resim
        $ogImage = trim($__env->yieldContent('og_image'));
        if ($ogImage === '') {
            try {
                $ogPage = strtok((string) request()->route()?->getName(), '.') ?: 'home';
                $ogImage = \App\Models\PageBanner::urls($ogPage, session('selected_city_id'))['desktop'];
            } catch (\Throwable) {
                $ogImage = asset('images/hero-banner.webp');
            }
        }
        $ogType = trim($__env->yieldContent('og_type')) ?: 'website';
    @endphp

    <title>{!! $pageTitle !!}</title>
    <meta name="description" content="{!! $pageDescription !!}" />
    <meta name="robots" content="@yield('robots', 'index, follow, max-image-preview:large, max-snippet:-1')" />
    <meta name="theme-color" content="#C62E2E" />
    <meta name="p:domain_verify" content="8b263291d9f5e7d042415fd68edaf422" />
    <meta name="yandex-verification" content="a7cedc75b9464208" />

    {{-- Canonical (izleme parametreleri olmadan) --}}
    <link rel="canonical" href="{{ $canonical }}" />

    {{-- Hreflang --}}
    @foreach ($locales as $locale => $properties)
        <link rel="alternate" hreflang="{{ $locale }}" href="{{ \App\Support\Schema::canonical($locale) }}">
    @endforeach
    <link rel="alternate" hreflang="x-default" href="{{ \App\Support\Schema::canonical('en') }}">

    {{-- Open Graph / Twitter: paylaşımda başlık, açıklama ve görsel --}}
    <meta property="og:site_name" content="TripSpoiler" />
    <meta property="og:type" content="{{ $ogType }}" />
    <meta property="og:title" content="{!! $pageTitle !!}" />
    <meta property="og:description" content="{!! $pageDescription !!}" />
    <meta property="og:url" content="{{ $canonical }}" />
    <meta property="og:image" content="{!! $ogImage !!}" />
    <meta property="og:locale" content="{{ $locales[$currentLocale]['regional'] ?? $currentLocale }}" />
    @foreach ($locales as $locale => $properties)
        @continue($locale === $currentLocale)
        <meta property="og:locale:alternate" content="{{ $properties['regional'] ?? $locale }}" />
    @endforeach
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="{!! $pageTitle !!}" />
    <meta name="twitter:description" content="{!! $pageDescription !!}" />
    <meta name="twitter:image" content="{!! $ogImage !!}" />
    @stack('meta')

    {{-- Bağlantıları önceden aç: font ve CDN --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="dns-prefetch" href="https://www.googletagmanager.com">
    {{-- Font CSS'i sayfa çizimini bekletmez (display=swap ile önce sistem fontu, font gelince değişir) --}}
    <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"></noscript>
    <link rel="manifest" href="{{ asset('site.webmanifest') . '?v=3' }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')

    @php
        $schema = [
            '@context' => 'https://schema.org',
            '@graph' => [
                [
                    '@type' => 'Organization',
                    '@id' => \App\Support\Schema::organizationId(),
                    'name' => 'TripSpoiler',
                    'url' => \App\Support\Schema::home(),
                    'logo' => [
                        '@type' => 'ImageObject',
                        '@id' => \App\Support\Schema::home() . '#logo',
                        'url' => \App\Support\Schema::logoUrl(),
                        'width' => 512,
                        'height' => 512,
                    ],
                    'image' => ['@id' => \App\Support\Schema::home() . '#logo'],
                    'description' => 'Curated city experiences, museum guides and travel insights from around the world.',
                    'foundingDate' => '2024',
                    'email' => 'info@tripspoiler.com',
                    'contactPoint' => [
                        '@type' => 'ContactPoint',
                        'contactType' => 'customer support',
                        'email' => 'info@tripspoiler.com',
                        'url' => route('contact'),
                        'availableLanguage' => array_values(array_map(fn ($p) => $p['name'], $locales)),
                    ],
                    'sameAs' => [
                        'https://www.instagram.com/tripspoilerofficial/',
                        'https://www.tiktok.com/@tripspoilerofficial',
                        'https://www.pinterest.com/tripspoiler/',
                    ],
                ],
                [
                    '@type' => 'WebSite',
                    '@id' => \App\Support\Schema::websiteId(),
                    'url' => \App\Support\Schema::home(),
                    'name' => 'TripSpoiler',
                    'publisher' => \App\Support\Schema::organizationRef(),
                    'inLanguage' => array_keys($locales),
                ],
            ],
        ];
    @endphp

    @jsonld($schema)
    {{-- Sürüm sabit: "3.x.x" her yeni sürümü habersiz çeker (bozulma / tedarik zinciri riski) --}}
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.9/dist/cdn.min.js" defer></script>
    {{-- Google Analytics: yalnızca ziyaretçi çerezleri kabul ettiyse yüklenir (partials/cookie-consent) --}}
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag() { dataLayer.push(arguments); }
        window.tsLoadAnalytics = function () {
            if (window.tsAnalyticsLoaded) return;
            window.tsAnalyticsLoaded = true;
            var s = document.createElement('script');
            s.async = true;
            s.src = 'https://www.googletagmanager.com/gtag/js?id=G-VKGSGTFYD1';
            document.head.appendChild(s);
            gtag('js', new Date());
            gtag('config', 'G-VKGSGTFYD1', { anonymize_ip: true });
        };
        if (document.cookie.split('; ').indexOf('ts_consent=all') !== -1) window.tsLoadAnalytics();
    </script>

</head>

<body class="bg-white text-slate-900 antialiased min-h-screen flex flex-col">

    @include('partials.announcement-bar')
    @include('partials.rates-bar')
    @include('partials.header')
    @include('partials.side-dock')

    <main class="flex-1">
        @yield('content')
    </main>
    

    @stack('scripts')
    
    @include('partials.partners-strip')
    @include('partials.footer')
    @include('partials.cookie-consent')

    {{-- Dil değişince sayfa aynı yerde kalsın: tıklanan dil bağlantısında kaydırma konumu saklanır, yeni sayfada geri yüklenir --}}
    <script>
        (() => {
            const key = 'ts-lang-scroll';
            const path = (url) => new URL(url, location.href).pathname.replace(/^\/[a-z]{2}(?=\/|$)/, '') || '/';
            document.addEventListener('click', (e) => {
                const link = e.target.closest('a[hreflang]');
                if (!link || e.metaKey || e.ctrlKey) return;
                try { sessionStorage.setItem(key, JSON.stringify({ path: path(link.href), y: window.scrollY })); } catch (err) {}
            });
            try {
                const saved = JSON.parse(sessionStorage.getItem(key) || 'null');
                sessionStorage.removeItem(key);
                if (saved && saved.path === path(location.href)) {
                    if ('scrollRestoration' in history) history.scrollRestoration = 'manual';
                    const restore = () => window.scrollTo({ top: saved.y, behavior: 'instant' });
                    restore();
                    window.addEventListener('load', restore, { once: true });
                }
            } catch (err) {}
        })();
    </script>

</body>

</html>
