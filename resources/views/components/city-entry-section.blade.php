{{--
    "Şehirler çağrısı": büyük resimli kart. Yazı doğrudan resmin üstünde; altta şehir şeridi (çok şehir olsa da tek satır, yana kayar).
    Yazılar ve resim panelden (Bölümler > Şehirler çağrısı); bölüm kapatılabilir.
--}}
@props(['cityId' => null])

@if (\App\Support\SiteSections::enabled('city_entry'))
    @php
        // Şehre özel (sayfa bölümlerinden): o şehrin banner resmi, ana düğme o şehrin sayfasına
        $focusCity = $cityId ? \App\Models\City::visible()->find($cityId) : null;
        $texts = \App\Models\SectionText::texts('city_entry', app()->getLocale(), [
            'eyebrow' => __('city-entry-section.eyebrow'), 'title' => __('city-entry-section.title'),
            'title_highlight' => __('city-entry-section.title_highlight'), 'description' => __('city-entry-section.text'),
        ]);
        $imagePath = \App\Models\Setting::many(['city_entry_image'])['city_entry_image'];
        // Şehre özel: o şehrin şehir sayfası resmi (panelde yüklenmişse)
        $cityBanner = $focusCity ? \App\Models\PageBanner::where('page_key', 'cities')->where('city_id', $focusCity->id)->value('image') : null;
        $image = $cityBanner ? \App\Models\ActivityImage::mediaUrl($cityBanner, 'md') : ($imagePath ? \App\Models\ActivityImage::mediaUrl($imagePath) : asset('images/tripspoiler-statement.webp'));
        // 50 şehir de olsa tek satır: ilk 12'si kayan şeritte, kalanı "+N" ile şehirler sayfasına
        // Şeritte sadece rehberi olan şehirler (boş şehir sayfasına götürmesin)
        $cityQuery = \App\Models\City::visible()->whereIn('id', \App\Http\Controllers\CityController::guideCityIds())->with('country')->orderByDesc('population');
        $cityTotal = (clone $cityQuery)->count();
        $cities = $cityQuery->limit(12)->get();
        // Şehre özel: o şehir şeritte ilk sırada
        if ($focusCity) {
            $cities = $cities->reject(fn ($c) => $c->is($focusCity))->prepend($focusCity->loadMissing('country'))->take(12);
        }
        // Ülke kodundan bayrak: "TR" → 🇹🇷
        $flag = fn ($code) => $code ? implode('', array_map(fn ($c) => mb_chr(0x1F1E6 + ord($c) - 65), str_split(strtoupper($code)))) : '';
    @endphp

    <section class="bg-white py-16 md:py-24">
        <div class="mx-auto max-w-7xl px-4">
            <div class="group relative isolate overflow-hidden rounded-[32px] bg-slate-900">

                {{-- RESİM --}}
                <img src="{{ $image }}" alt="{{ __('city-entry-section.alt') }}" loading="lazy" decoding="async"
                    class="absolute inset-0 -z-20 h-full w-full object-cover transition duration-[1.6s] ease-[cubic-bezier(0.22,1,0.36,1)] group-hover:scale-[1.04]">
                <div class="absolute inset-0 -z-10 bg-gradient-to-r from-slate-950/75 via-slate-950/30 to-transparent rtl:bg-gradient-to-l"></div>
                <div class="absolute inset-x-0 bottom-0 -z-10 h-2/3 bg-gradient-to-t from-slate-950/85 via-slate-950/40 to-transparent"></div>

                <div class="flex min-h-[32rem] flex-col justify-end md:min-h-[38rem]">

                    {{-- YAZI: doğrudan resmin üstünde (kutu yok) --}}
                    <div class="max-w-2xl px-6 pb-8 pt-48 text-white sm:px-10 lg:px-14 lg:pb-10">
                        @if (filled($texts['eyebrow']))
                            <p class="inline-flex items-center gap-2 text-[11px] font-bold uppercase tracking-[0.2em] text-white/80">
                                <span class="h-px w-6 bg-[#FF6B6E]"></span>{{ $texts['eyebrow'] }}
                            </p>
                        @endif
                        <h2 class="mt-4 text-3xl font-extrabold leading-[1.05] tracking-[-0.025em] drop-shadow-[0_2px_20px_rgba(0,0,0,0.35)] sm:text-4xl lg:text-[52px]">
                            {{ $texts['title'] }} <span class="text-[#FF6B6E]">{{ $texts['title_highlight'] }}</span>
                        </h2>
                        @if (filled($texts['description']))
                            <p class="mt-4 max-w-xl text-base leading-relaxed text-white/85 md:text-lg">{{ $texts['description'] }}</p>
                        @endif
                        <div class="mt-7 flex flex-wrap gap-3">
                            <a href="{{ $focusCity ? route('cities.show', $focusCity->slug) : route('cities.index') }}" class="inline-flex items-center gap-2 rounded-xl bg-white px-5 py-3 text-sm font-semibold text-slate-900 transition hover:bg-[#C62E2E] hover:text-white active:scale-[0.98]">
                                {{ $focusCity ? __('city-entry-section.city_button', ['city' => $focusCity->name]) : __('city-entry-section.button') }} <span aria-hidden="true" class="rtl:rotate-180">→</span>
                            </a>
                            <a href="{{ \App\Support\PageSections::activitiesLink($focusCity ? ['city' => $focusCity->slug] : []) }}" class="inline-flex items-center gap-2 rounded-xl px-5 py-3 text-sm font-semibold text-white ring-1 ring-white/40 transition hover:bg-white/10">
                                {{ __('city-entry-section.secondary') }}
                            </a>
                        </div>
                    </div>

                    {{-- ŞEHİRLER: alt şeritte tek satır, yana kayar --}}
                    @if ($cities->isNotEmpty())
                        <div class="flex items-center gap-4 border-t border-white/15 px-6 py-4 sm:px-10 lg:px-14">
                            <p class="hidden shrink-0 text-[11px] font-bold uppercase tracking-[0.18em] text-white/60 sm:block">{{ __('city-entry-section.cities_label') }}</p>
                            <div class="-my-1 flex min-w-0 flex-1 gap-2 overflow-x-auto py-1 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                                @foreach ($cities as $city)
                                    <a href="{{ route('cities.show', $city->slug) }}"
                                        @class(['inline-flex shrink-0 items-center gap-2 rounded-full px-3.5 py-2 text-sm font-semibold ring-1 transition', 'bg-white text-slate-900 ring-white' => $focusCity?->is($city), 'bg-white/10 text-white ring-white/20 hover:bg-white hover:text-slate-900' => !$focusCity?->is($city)])>
                                        <span class="text-base leading-none" aria-hidden="true">{{ $flag($city->country?->code) }}</span>
                                        {{ $city->name }}
                                    </a>
                                @endforeach
                                @if ($cityTotal > $cities->count())
                                    <a href="{{ route('cities.index') }}" class="inline-flex shrink-0 items-center rounded-full bg-[#C62E2E] px-3.5 py-2 text-sm font-semibold text-white transition hover:bg-[#A82424]">
                                        +{{ $cityTotal - $cities->count() }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endif
