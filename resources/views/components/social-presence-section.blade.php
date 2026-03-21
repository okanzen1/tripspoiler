@props([
    'title' => __('social-presence-section.title'),
    'text' => __('social-presence-section.text'),
    'color' => 'bg-[#FFFAF9]',
])

<section
    class="relative {{ $color }} py-20 md:py-28 overflow-hidden">

    {{-- Ambient glow --}}
    <div class="absolute inset-0 pointer-events-none">
        <div class="absolute right-0 bottom-0 w-[500px] h-[500px] bg-[#C62E2E]/5 rounded-full blur-[140px]"></div>
    </div>

    <div class="relative max-w-7xl mx-auto px-4">

        <div class="grid lg:grid-cols-2 gap-16 items-stretch">

            {{-- LEFT TEXT --}}
            <div>

                <span
                    class="inline-block text-xs font-semibold tracking-wide uppercase
                             text-[#C62E2E] bg-[#C62E2E]/10 px-4 py-1 rounded-full">
                    {{ __('social-presence-section.badge') }}
                </span>

                <h2 class="mt-6 text-3xl md:text-5xl font-bold text-slate-900 leading-tight">
                    {{ $title }}
                </h2>

                <p class="mt-6 text-lg text-slate-600 leading-relaxed max-w-xl">
                    {{ $text }}
                </p>

                <div class="mt-8 space-y-4">

                    {{-- Instagram --}}
                    <a href="https://instagram.com/tripspoilerofficial" target="_blank"
                        class="group flex items-center justify-between
                              px-6 py-4 rounded-2xl
                              bg-gradient-to-r from-pink-500 to-orange-500
                              text-white font-semibold
                              transition-all duration-300 hover:scale-[1.02]">

                        <span>{{ __('social-presence-section.instagram') }}</span>
                        <span class="opacity-80 group-hover:translate-x-1 transition">→</span>
                    </a>

                    {{-- TikTok --}}
                    <a href="https://tiktok.com/@tripspoilerofficial" target="_blank"
                        class="group flex items-center justify-between
                              px-6 py-4 rounded-2xl
                              bg-black text-white font-semibold
                              transition-all duration-300 hover:scale-[1.02]">

                        <span>{{ __('social-presence-section.tiktok') }}</span>
                        <span class="opacity-80 group-hover:translate-x-1 transition">→</span>
                    </a>

                </div>

            </div>

            {{-- RIGHT IMAGES --}}
            <div class="flex items-center">
                <div class="grid grid-cols-2 gap-6 w-full max-h-[380px]">

                    {{-- Instagram Image --}}
                    <a href="https://instagram.com/tripspoilerofficial" target="_blank"
                        class="relative rounded-[28px] overflow-hidden h-[380px]
                              transform transition duration-300
                              hover:-translate-y-2 hover:scale-[1.03]">

                        <img src="{{ asset('images/social-instagram.webp') }}" alt="TripSpoiler Instagram"
                            class="w-full h-full object-cover">
                    </a>

                    {{-- TikTok Image --}}
                    <a href="https://tiktok.com/@tripspoilerofficial" target="_blank"
                        class="relative rounded-[28px] overflow-hidden h-[380px]
                              transform transition duration-300
                              hover:-translate-y-2 hover:scale-[1.03]">

                        <img src="{{ asset('images/social-tiktok.webp') }}" alt="TripSpoiler TikTok"
                            class="w-full h-full object-cover">
                    </a>

                </div>
            </div>

        </div>

    </div>

</section>
