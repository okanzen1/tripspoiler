<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="icon" href="{{ asset('favicon.ico') }}">

    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">

    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">

    <link rel="manifest" href="{{ asset('site.webmanifest') }}">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css">


    <title>@yield('title', 'TripSpoiler')</title>
    <meta name="description" content="@yield('meta_description', 'Travel guides and museum tips')" />
    <meta name="p:domain_verify" content="8b263291d9f5e7d042415fd68edaf422" />
    <meta name="yandex-verification" content="a7cedc75b9464208" />

    {{-- Canonical --}}
    <link rel="canonical" href="{{ url()->current() }}" />

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

    @include('partials.header')

    <main class="flex-1">
        @yield('content')
    </main>

    @stack('scripts')
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
    @include('partials.footer')

</body>

</html>
