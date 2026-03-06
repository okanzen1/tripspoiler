@if (!empty($blogs) && count($blogs))
    @php
        $isHome = request()->routeIs('home');
    @endphp

    <section class="bg-[#F7F9FB] {{ $isHome ? 'pt-5 md:pt-10 pb-15 md:pb-30' : 'pt-0 md:pt-10' }}">

        <div class="max-w-7xl mx-auto {{ $isHome ? 'px-4' : 'px-0' }}">

            {{-- HEADER --}}
            <div class="mb-10">

                @if ($cityName)
                    <h2 class="text-3xl md:text-4xl font-bold text-slate-900 leading-tight">
                        <span class="text-[#C62E2E]">{{ $cityName }}</span>{{ __('most-popular-blog.city_title_suffix') }}
                    </h2>

                    <p class="text-slate-600 mt-3 text-lg">
                        {{ __('most-popular-blog.city_desc') }}
                    </p>
                @else
                    <h2 class="text-3xl md:text-4xl font-bold text-slate-900 leading-tight">
                       {!! __('most-popular-blog.title') !!}
                    </h2>

                    <p class="text-slate-600 mt-3 text-lg">
                        {{ __('most-popular-blog.desc') }}
                    </p>
                @endif

            </div>

            {{-- GRID --}}
            <div class="grid md:grid-cols-3 gap-12 items-start">

                {{-- BLOG LIST --}}
                <div class="md:col-span-2">

                    @foreach ($blogs as $blog)
                        <a href="{{ route('blog.show', ['slug' => $blog['slug']]) }}"
                            class="group block bg-white rounded-2xl border border-slate-200
                        px-6 py-5 mb-4
                        shadow-sm
                        transition-all duration-300 ease-out
                        transform
                        hover:-translate-y-1
                        hover:shadow-xl
                        hover:scale-[1.01]">

                            <div class="text-sm font-semibold text-[#C62E2E]">
                                {{ $blog['title'] }}
                            </div>

                            <div class="mt-2 text-slate-900">
                                {{ \Illuminate\Support\Str::limit($blog['excerpt'], 140) }}
                            </div>
                        </a>
                    @endforeach

                    {{-- RED CTA NOW INSIDE SAME COLUMN --}}
                    <a href="{{ route('blog.index') }}"
                        class="group block mt-4 rounded-2xl
                        bg-gradient-to-r 
                        from-[#D94A4A] 
                        via-[#C62E2E] 
                        to-[#B91C1C]
                        px-6 py-6 md:px-10 md:py-8
                        flex flex-col md:flex-row md:items-center justify-between
                        gap-6 overflow-hidden

                        shadow-sm
                        transition-all duration-300 ease-out
                        transform
                        hover:-translate-y-1
                        hover:shadow-xl
                        hover:scale-[1.01]">

                        <div class="max-w-xl">

                            @if ($cityName)
                                <h3 class="text-lg md:text-xl font-semibold text-white leading-snug">
                                    {{ __('most-popular-blog.cta_city_title_prefix') }} {{ $cityName }}{{ __('most-popular-blog.cta_city_title_suffix') }}
                                </h3>

                                <p class="mt-2 text-white/80 text-sm md:text-base leading-relaxed">
                                    {{ __('most-popular-blog.cta_city_desc_prefix') }} {{ $cityName }} {{ __('most-popular-blog.cta_city_desc_suffix') }}
                                </p>
                            @else
                                <h3 class="text-lg md:text-xl font-semibold text-white leading-snug">
                                    {{ __('most-popular-blog.cta_title') }}
                                </h3>

                                <p class="mt-2 text-white/80 text-sm md:text-base leading-relaxed">
                                    {{ __('most-popular-blog.cta_desc') }}
                                </p>
                            @endif
                        </div>

                        <div
                            class="text-white text-sm font-semibold
                                    flex items-center
                                    transition-all duration-300
                                    group-hover:tracking-wide">

                            <span class="border-b border-white/60 pb-1 group-hover:border-white">
                                {{ __('most-popular-blog.browse') }}
                            </span>

                            <span class="ml-3 transition-transform duration-300 group-hover:translate-x-2">
                                →
                            </span>
                        </div>
                    </a>

                </div>

                {{-- NEWSLETTER --}}
                <div class="md:pt-0">
                    <x-newsletter-subscribe />
                </div>
            </div>
    </section>


@endif