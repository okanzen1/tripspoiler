{{-- "… gezi rehberleri": aktivite / yer sayfasının altında ilgili blog yazıları (App\Support\RelatedPosts) --}}
@if ($posts->isNotEmpty())
    <section class="bg-slate-50 py-12 md:py-16">
        <div class="mx-auto max-w-7xl px-4">
            <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-slate-400">{{ __('blog.blog') }}</p>
            <h2 class="mt-2 text-2xl font-extrabold tracking-[-0.02em] text-slate-900 md:text-3xl">
                {{ $city ? __('blog.guides_city', ['city' => $city->name]) : __('blog.related_all') }}
            </h2>
            <div class="mt-7 grid gap-x-4 gap-y-8 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($posts as $post)
                    <a href="{{ route('blog.show', $post->localSlug()) }}" class="group block">
                        <div class="relative aspect-[4/3] overflow-hidden rounded-[22px] bg-slate-200 ring-1 ring-slate-900/5">
                            @if ($post->cover_path)
                                <img src="{{ $post->coverUrl('sm') }}" alt="{{ $post->title }}" loading="lazy" decoding="async"
                                    class="h-full w-full object-cover transition duration-700 ease-[cubic-bezier(0.22,1,0.36,1)] group-hover:scale-[1.06]">
                            @endif
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/55 via-transparent to-transparent opacity-80 transition group-hover:opacity-100"></div>
                            <span class="absolute bottom-3 left-3 inline-flex items-center gap-1 text-[11px] font-semibold text-white/90 rtl:left-auto rtl:right-3">
                                <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="8.5"/><path d="M12 7.5V12l3 2"/></svg>
                                {{ __('blog-page.min_read', ['count' => $post->readingMinutes()]) }}
                            </span>
                        </div>
                        <h3 class="mt-3.5 line-clamp-2 text-[17px] font-extrabold leading-snug tracking-[-0.01em] text-slate-900 transition group-hover:text-[#C62E2E]">{{ $post->title }}</h3>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endif
