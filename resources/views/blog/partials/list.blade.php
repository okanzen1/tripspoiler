<section class="bg-white pb-6 my-16">
    <div class="max-w-6xl mx-auto px-4">
        @forelse ($blogs as $blog)
            <h2 class="text-xl font-bold text-slate-900 mb-6">
                Featured Posts
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <a href="{{ route('blog.show', [
                    'slug' => $blog->getTranslation('slug', $locale),
                ]) }}"
                    class="block bg-white border border-[#F3D6D1] rounded-3xl shadow-sm overflow-hidden hover:shadow-lg transition">

                    @if ($blog->images->isNotEmpty())
                        @php
                            $image = $blog->images->first();
                        @endphp

                        <img src="{{ route('images.view', $image->id) }}" alt="{{ $blog->name }}"
                            class="h-44 w-full object-cover">
                    @else
                        <div class="h-44 w-full object-cover"></div>
                    @endif

                    <div class="p-6">
                        <span class="text-xs font-semibold text-[#C62E2E]">
                            {{ $blog->city->getTranslation('name', $locale) }}
                        </span>

                        <h3 class="font-semibold text-slate-900 mt-2">
                            {{ $blog->getTranslation('title', $locale) }}
                        </h3>

                        <p class="text-sm text-slate-600 mt-2">
                            {{ Str::limit($blog->getTranslation('excerpt', $locale), 100) }}
                        </p>
                    </div>
                </a>
            </div>
        @empty
            <p class="text-slate-500">No featured posts found.</p>
        @endforelse
    </div>
</section>