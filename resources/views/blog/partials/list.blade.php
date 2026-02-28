<section class="bg-white my-16">
    <div class="max-w-7xl mx-auto px-4">

        @if(count($blogs) > 0)

            <h2 class="text-xl md:text-2xl font-bold text-slate-900">
                Stories Beyond the Surface
            </h2>

            <p class="text-slate-600 text-base md:text-lg max-w-2xl mb-8 mt-1">
                Thoughtful stories and local insights to help you see the city beyond its landmarks.
            </p>

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-5 items-stretch">

                @foreach ($blogs as $blog)
                    @php
                        $image = $blog->images->first();
                    @endphp

                    <a href="{{ route('blog.show', [
                        'slug' => $blog->getTranslation('slug', $locale),
                    ]) }}"
                       class="group block h-full">

                        <div class="relative h-[260px] md:h-[320px]
                                    rounded-2xl overflow-hidden
                                    shadow-[0_6px_18px_rgba(0,0,0,0.06)]
                                    hover:shadow-[0_12px_28px_rgba(0,0,0,0.10)]
                                    transition-all duration-300">

                            {{-- IMAGE --}}
                            @if ($image)
                                <img src="{{ route('images.view', $image->id) }}"
                                     alt="{{ $blog->name }}"
                                     class="w-full h-full object-cover
                                            transition duration-700
                                            group-hover:scale-105">
                            @else
                                <div class="w-full h-full bg-gradient-to-b from-slate-100 to-slate-200"></div>
                            @endif

                            {{-- DARK GRADIENT OVERLAY --}}
                            <div class="absolute inset-0 
                                        bg-gradient-to-t 
                                        from-black/55 
                                        via-black/20 
                                        to-transparent">
                            </div>

                            {{-- TEXT CONTENT --}}
                            <div class="absolute inset-0 flex flex-col justify-end p-5">

                                {{-- CITY --}}
                                <span class="text-xs text-white/80 mb-2 tracking-wide">
                                    {{ $blog->city->getTranslation('name', $locale) }}
                                </span>

                                {{-- TITLE --}}
                                <h3 class="text-sm md:text-base font-semibold text-white leading-snug line-clamp-2 drop-shadow-lg">
                                    {{ Str::limit($blog->getTranslation('title', $locale), 60) }}
                                </h3>

                                {{-- CTA --}}
                                <span class="mt-3 text-sm font-medium text-white/90 
                                            inline-flex items-center gap-1 
                                            group-hover:translate-x-1 
                                            transition duration-300">
                                    Read story →
                                </span>

                            </div>

                        </div>

                    </a>

                @endforeach

            </div>

        @endif

    </div>
</section>