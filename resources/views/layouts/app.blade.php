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
        @yield('modals')

    </main>

    @stack('scripts')
    @include('partials.footer')

    @if (!request()->routeIs('contact*'))
        <a id="contact-float" href="{{ route('contact') }}"
            class="fixed right-0 bottom-0 z-50
                translate-x-3 translate-y-3
                w-36 h-36 md:w-44 md:h-44
                transition-transform duration-300 hover:scale-110">

            <img src="{{ asset('images/contact-bunny.png') }}" alt="Contact"
                class="w-full h-full object-contain drop-shadow-2xl">
        </a>

        <script>
            document.addEventListener("DOMContentLoaded", function() {
                const button = document.getElementById("contact-float");
                const footer = document.getElementById("site-footer");
                if (!button || !footer) return;

                function adjust() {
                    const footerRect = footer.getBoundingClientRect();
                    const windowHeight = window.innerHeight;

                    if (footerRect.top < windowHeight) {
                        const overlap = windowHeight - footerRect.top;
                        button.style.bottom = overlap + "px";
                    } else {
                        button.style.bottom = "0px";
                    }
                }

                adjust();
                window.addEventListener("scroll", adjust, {
                    passive: true
                });
                window.addEventListener("resize", adjust);
            });
        </script>
    @endif

</body>

</html>
