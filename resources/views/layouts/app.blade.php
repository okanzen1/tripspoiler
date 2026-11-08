<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ LaravelLocalization::getCurrentLocaleDirection() }}">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="icon" href="{{ asset('favicon.ico') . '?v=3' }}">

    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') . '?v=3' }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') . '?v=3' }}">

    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') . '?v=3' }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>
    <link rel="manifest" href="{{ asset('site.webmanifest') . '?v=3' }}">


    <title>@yield('title', 'TripSpoiler')</title>
    <meta name="description" content="@yield('meta_description', 'Travel guides and museum tips')" />
    <meta name="p:domain_verify" content="8b263291d9f5e7d042415fd68edaf422" />
    <meta name="yandex-verification" content="a7cedc75b9464208" />

    {{-- Canonical --}}
   <link rel="canonical" href="{{ LaravelLocalization::getLocalizedURL(app()->getLocale(), null, [], true) }}" />

    {{-- Hreflang --}}
    @foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties)
        <link rel="alternate" hreflang="{{ $locale }}"
            href="{{ LaravelLocalization::getLocalizedURL($locale, null, [], true) }}">
    @endforeach

    {{-- x-default --}}
    <link rel="alternate" hreflang="x-default" href="{{ LaravelLocalization::getLocalizedURL('en', null, [], true) }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')

    @php
        $schema = [
            '@context' => 'https://schema.org',
            '@graph' => [
                [
                    '@type' => 'Organization',
                    '@id' => config('app.url') . '#organization',
                    'name' => 'TripSpoiler',
                    'url' => config('app.url'),
                    'logo' => [
                        '@type' => 'ImageObject',
                        'url' => asset('android-chrome-512x512.png'),
                    ],
                    'description' =>
                        'Curated city experiences, museum guides and travel insights from around the world.',
                    'foundingDate' => '2024',
                    'sameAs' => [
                        'https://www.instagram.com/tripspoilerofficial/',
                        'https://www.tiktok.com/@tripspoilerofficial',
                        'https://www.pinterest.com/tripspoiler/',
                    ],
                ],

                [
                    '@type' => 'WebSite',
                    '@id' => config('app.url') . '#website',
                    'url' => config('app.url'),
                    'name' => 'TripSpoiler',
                    'publisher' => [
                        '@id' => config('app.url') . '#organization',
                    ],
                    'inLanguage' => app()->getLocale(),
                ],
            ],
        ];
    @endphp

    <script type="application/ld+json">
        {!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-VKGSGTFYD1"></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());

        gtag('config', 'G-VKGSGTFYD1');
    </script>

</head>

<body class="bg-white text-slate-900 antialiased min-h-screen flex flex-col">

    @include('partials.announcement-bar')
    @include('partials.header')
    @include('partials.side-dock')

    <main class="flex-1">
        @yield('content')
    </main>
    

    @stack('scripts')
    
    @include('partials.partners-strip')
    @include('partials.footer')

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
