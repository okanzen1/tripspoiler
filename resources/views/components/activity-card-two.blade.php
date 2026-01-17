@if (count($venues) > 0)
    @foreach ($venues as $item)
        <section class="bg-white py-8">
            <div class="max-w-6xl mx-auto px-4">

                <a href="{{ $item->affiliate_link }}" target="_blank" rel="nofollow sponsored"
                    class="group block border border-[#F3D6D1] rounded-3xl overflow-hidden
                    bg-[#FFF5F3] hover:shadow-lg transition">

                    <div class="grid grid-cols-1 md:grid-cols-5 items-stretch">

                        {{-- IMAGE --}}
                        <div class="md:col-span-2">
                            @if ($item->images->isNotEmpty())
                                @php
                                    $image = $item->images->first();
                                @endphp

                                <img src="{{ route('images.view', $image->id) }}" alt="{{ $item->name }}"
                                    class="h-52 md:h-full w-full object-cover">
                            @else
                                <div class="h-52 md:h-full w-full bg-gray-200"></div>
                            @endif
                        </div>


                        {{-- CONTENT --}}
                        <div class="md:col-span-3 p-6 md:p-8 flex flex-col justify-between">

                            <div>
                                <span class="text-xs font-semibold text-[#C62E2E] tracking-wide">
                                    Recommended experience
                                </span>

                                <h3 class="mt-2 text-lg md:text-xl font-bold text-slate-900 leading-snug">
                                    {{ $item->name }}
                                </h3>

                                <p class="mt-3 text-slate-600 text-sm max-w-xl leading-relaxed">
                                    {{ $item->description }}
                                </p>
                            </div>

                            <div class="mt-5 flex justify-end">
                                <span
                                    class="inline-flex items-center gap-2
                                    text-sm font-semibold text-white
                                    bg-[#C62E2E] px-5 py-2.5 rounded-full
                                    transition group-hover:bg-[#A82424]">
                                    View deals →
                                </span>
                            </div>

                        </div>

                    </div>
                </a>

            </div>
        </section>
    @endforeach
@endif
