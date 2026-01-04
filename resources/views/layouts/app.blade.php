<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <title>@yield('title', 'TripSpoiler')</title>
    <meta name="description" content="@yield('meta_description', 'Travel guides and museum tips')" />

    @vite('resources/css/app.css')
</head>

<body class="bg-slate-50 text-slate-900 antialiased min-h-screen flex flex-col">

    @include('partials.header')

    <main class="flex-1">
        @yield('content')
    </main>

    @include('partials.footer')

    @vite('resources/js/app.js')
</body>
</html>
