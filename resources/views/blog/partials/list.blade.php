<section class="bg-white my-16">
    <div class="max-w-6xl mx-auto px-4">

        @if(count($blogs) > 0)

            <div class="mb-6">
                <h2 class="text-xl md:text-2xl font-bold text-slate-900">
                    Featured Stories
                </h2>
            </div>

            <div
                class="flex gap-4
                       md:grid md:grid-cols-5 md:gap-5
                       overflow-x-auto md:overflow-visible
                       scroll-smooth
                       snap-x snap-mandatory
                       pb-3 md:pb-0
                       [-ms-overflow-style:none]
                       [scrollbar-width:none]
                       [&::-webkit-scrollbar]:hidden">

                @foreach ($blogs as $blog)

                    <a href="{{ route('blog.show', [
                        'slug' => $blog->getTranslation('slug', $locale),
                    ]) }}"
                       class="group relative
                              min-w-[180px] md:min-w-0
                              aspect-[4/5]
                              rounded-xl
                              overflow-hidden
                              bg-slate-50
                              shadow-sm
                              hover:shadow-lg
                              transition
                              duration-300
                              snap-start">

                        @if ($blog->images->isNotEmpty())
                            @php $image = $blog->images->first(); @endphp
                            <img src="{{ route('images.view', $image->id) }}"
                                 alt="{{ $blog->name }}"
                                 class="absolute inset-0 w-full h-full object-cover
                                        transition duration-500
                                        group-hover:scale-105">
                        @endif

                        <div class="absolute inset-0
                                    bg-gradient-to-t
                                    from-black/70
                                    via-black/30
                                    to-transparent">
                        </div>

                        <div class="absolute bottom-0 p-3 text-white">
                            <span class="text-[10px] font-medium
                                         bg-white/20 backdrop-blur
                                         px-2 py-1 rounded-full mb-2 inline-block">
                                {{ $blog->city->getTranslation('name', $locale) }}
                            </span>

                            <h3 class="text-[13px] font-semibold leading-snug">
                                {{ Str::limit($blog->getTranslation('title', $locale), 55) }}
                            </h3>
                        </div>

                    </a>

                @endforeach

            </div>

        @endif

    </div>
</section>