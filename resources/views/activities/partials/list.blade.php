@if ($activities->count() === 0)
    <section class="bg-white py-10">
        <div class="max-w-6xl mx-auto px-4">
            <p class="text-slate-700">
                No activities or city passes available at the moment. Please check back later!
            </p>
        </div>
    </section>
@else
    {{-- SEO INTRO TEXT --}}
    @php
        $content = trim($pageContent?->getTranslation('content', app()->getLocale()) ?? '');
        // Quill boş html temizliği
        $content = str_replace(['<p><br></p>', '<br>', '&nbsp;'], '', $content);
    @endphp

    @if ($content !== '')
        <section class="bg-white py-10">
            <div class="max-w-6xl mx-auto px-4">
                <div class="mt-3 prose prose-slate leading-relaxed">
                    {!! $pageContent->getTranslation('content', app()->getLocale()) !!}
                </div>
            </div>
        </section>
    @endif

    {{-- ACTIVITIES & CITY PASSES --}}
    @if ($activities->count() > 0)
        <section class="bg-white pb-14">
            <div class="max-w-6xl mx-auto px-4">

                <h2 class="text-xl font-bold text-slate-900 mb-6">
                    Activities & City Passes
                </h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">

                    @foreach ($activities as $activity)
                        @php
                            $title = $activity->getTranslation('name', $locale);
                            $slug = $activity->getTranslation('slug', $locale);
                            $image = $activity->images->first();
                        @endphp

                        <div
                            class="bg-white border border-[#F3D6D1] rounded-3xl shadow-sm overflow-hidden hover:shadow-lg transition">

                            {{-- IMAGE --}}
                            @if ($image)
                                <img src="{{ route('images.view', $image->id) }}" alt="{{ $title }}"
                                    class="w-full h-40 object-cover">
                            @else
                                <img src="https://picsum.photos/600/400?random={{ $activity->id }}"
                                    alt="{{ $title }}" class="w-full h-40 object-cover">
                            @endif

                            <div class="p-5">

                                {{-- TITLE --}}
                                <h3 class="font-semibold text-slate-900 leading-snug">
                                    {{ $title }}
                                </h3>

                                {{-- TYPE BADGE (opsiyonel ama güzel) --}}
                                @if ($activity->activity_type === 'pass')
                                    <span
                                        class="inline-block mt-2 text-xs font-semibold bg-[#C62E2E]/10 text-[#C62E2E] px-3 py-1 rounded-full">
                                        City Pass
                                    </span>
                                @endif

                                {{-- CTA --}}
                                <div class="mt-4 flex items-center justify-end">
                                    <a href="{{ $activity->affiliate_link }}" target="_blank" rel="nofollow sponsored"
                                        class="text-sm font-semibold text-[#C62E2E] hover:underline">
                                        View details →
                                    </a>
                                </div>

                            </div>
                        </div>
                    @endforeach

                </div>
            </div>
        </section>
    @endif
@endif
