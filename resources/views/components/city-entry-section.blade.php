@props([
    'image' => 'images/tripspoiler-statement.png',
    'alt' => __('city-entry-section.alt'),
    'eyebrow' => __('city-entry-section.eyebrow'),
    'title' => __('city-entry-section.title'),
    'text' => __('city-entry-section.text'),
    'buttonText' => __('city-entry-section.button'),
    'buttonUrl' => '/cities',
])

<section class="relative bg-[#F7F9FB] py-16 md:py-24">

    <div class="relative max-w-7xl mx-auto px-4">
        <div
            class="relative overflow-hidden rounded-3xl
                    shadow-[0_25px_70px_rgba(0,0,0,0.12)]
                    group">

            {{-- IMAGE --}}
            <img src="{{ asset($image) }}" alt="{{ $alt }}"
                class="w-full h-[460px] md:h-[580px] object-cover
                       transition-transform duration-700 group-hover:scale-105">

            {{-- OVERLAY --}}
            <div
                class="absolute inset-0 bg-gradient-to-t
                        from-black/60 via-black/25 to-transparent">
            </div>

            {{-- CONTENT --}}
            <div class="absolute inset-0 flex items-end">
                <div class="w-full p-6 md:p-12">

                    <div class="max-w-2xl">

                        {{-- EYEBROW --}}
                        <span
                            class="inline-flex items-center
                                     text-xs font-semibold tracking-wide uppercase
                                     text-white/90 bg-white/10
                                     px-4 py-1.5 rounded-full backdrop-blur">
                            {{ $eyebrow }}
                        </span>

                        {{-- TITLE --}}
                        <h2 class="mt-6 text-3xl md:text-5xl font-bold text-white leading-tight">
                            {{ $title }}
                        </h2>

                        {{-- TEXT --}}
                        <p class="mt-5 text-white/85 text-base md:text-lg leading-relaxed">
                            {{ $text }}
                        </p>

                        {{-- BUTTON --}}
                        <div class="mt-8">
                            <a href="{{ url($buttonUrl) }}"
                                class="inline-flex items-center justify-center
                                      px-9 py-3 rounded-full
                                      bg-white text-slate-900
                                      text-sm font-semibold
                                      transition-all duration-300
                                      hover:bg-[#C62E2E] hover:text-white
                                      hover:scale-[1.03]">
                                {{ $buttonText }}
                            </a>
                        </div>

                    </div>

                </div>
            </div>

        </div>
    </div>

</section>
