<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="icon" href="{{ asset('favicon.ico') }}">

    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">

    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">

    <link rel="manifest" href="{{ asset('site.webmanifest') }}">


    <title>@yield('title', 'TripSpoiler')</title>
    <meta name="description" content="@yield('meta_description', 'Travel guides and museum tips')" />
    <meta name="p:domain_verify" content="8b263291d9f5e7d042415fd68edaf422" />
    <meta name="yandex-verification" content="a7cedc75b9464208" />


    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>

<body class="bg-white text-slate-900 antialiased min-h-screen flex flex-col">

    @include('partials.header')

    <main class="flex-1">
        @yield('content')
    </main>

    @stack('scripts')
    @include('partials.footer')

</body>

</html>
