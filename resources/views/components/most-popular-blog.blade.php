@if (!empty($blogs) && count($blogs))
    <section class="bg-[#F7F9FB] py-20 mb-10">

        <div class="max-w-7xl mx-auto px-4">

            {{-- HEADER --}}
            <div class="mb-10">
                <h2 class="text-3xl md:text-4xl font-bold text-slate-900">
                    Most read on the blog
                </h2>

                <p class="text-slate-600 mt-3 text-lg">
                    Short blog posts on a wide range of topics, from quick reads to deeper articles you can explore
                    anytime.
                </p>
            </div>

            {{-- GRID --}}
            <div class="grid md:grid-cols-3 gap-12 items-start">

                {{-- BLOG LIST --}}
                <div class="md:col-span-2">
                    @foreach ($blogs as $blog)
                        <a href="{{ route('blog.show', ['slug' => $blog['slug']]) }}"
                            class="block bg-white rounded-2xl border border-slate-200
                        px-6 py-5 mb-4
                        shadow-sm hover:shadow-md
                        transition duration-300">

                            <div class="text-sm font-semibold text-[#C62E2E]">
                                {{ $blog['title'] }}
                            </div>

                            <div class="mt-2 text-slate-900">
                                {{ \Illuminate\Support\Str::limit($blog['excerpt'], 140) }}
                            </div>
                        </a>
                    @endforeach
                </div>

                {{-- NEWSLETTER --}}
                <div class="md:pt-0">
                    <x-newsletter-subscribe />
                </div>

            </div>
        </div>
    </section>
@endif
