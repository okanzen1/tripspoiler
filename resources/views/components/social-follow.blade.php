{{--
    "Bizi takip et" kutusu (sayfa üstlerinde, koyu zemin): cam kutu, markaların kendi renklerinde ikonlar sırayla zıplar,
    kutudan ışık geçer (resources/css/app.css: social-sweep / social-bob / social-ring). Hesaplar App\Support\Socials.
--}}
@props(['center' => false])

<div {{ $attributes->class(['social-sweep relative inline-flex max-w-full items-center gap-4 overflow-hidden rounded-2xl bg-white/10 p-2 pl-4 ring-1 ring-white/15 backdrop-blur-md', 'mx-auto' => $center]) }}>
    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-white/70">{{ __('footer.follow') }}</p>
    <div class="flex shrink-0 items-center gap-2">
        @foreach (\App\Support\Socials::all() as [$name, $url, $handle, $icon, $tone])
            <span class="relative" style="--d: {{ $loop->index * 0.45 }}s">
                <span class="social-ring absolute inset-0 rounded-xl ring-2 ring-white/60" aria-hidden="true"></span>
                <a href="{{ $url }}" target="_blank" rel="noopener" aria-label="{{ $name }}" title="{{ $name }}"
                    class="social-bob relative grid size-10 place-items-center rounded-xl text-white ring-1 ring-white/20 transition duration-300 hover:scale-110 hover:[animation:none] {{ $tone }}">
                    <svg class="size-[18px]" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">{!! $icon !!}</svg>
                </a>
            </span>
        @endforeach
    </div>
</div>
