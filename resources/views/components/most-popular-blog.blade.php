@if (!empty($blogs) && count($blogs))
    <section class="bg-[#F7F9FB] py-14">
        <div class="max-w-6xl mx-auto px-4 grid md:grid-cols-3 gap-10 items-start">

            {{-- BLOG LIST --}}
            <div class="md:col-span-2">
                <h2 class="text-2xl md:text-3xl font-bold text-slate-900">
                    Most read on the blog
                </h2>

                <p class="text-slate-600 mt-2 mb-5 max-w-xl">
                    Short, honest pieces about trips that actually happened.
                </p>

                @foreach ($blogs as $blog)
                    <a href="#"
                        class="block bg-white rounded-2xl border border-slate-100 px-5 py-4 hover:shadow-md transition">
                        <div class="text-sm font-semibold text-[#C62E2E]">{{ $blog['title'] }}</div>
                        <div class="mt-1 font-semibold text-slate-900">
                            {{ $blog['excerpt'] }}
                        </div>
                    </a>
                @endforeach
            </div>

            {{-- NEWSLETTER --}}
            <x-newsletter-subscribe />

        </div>
    </section>
@endif
