{{--
    Kapak resmi kesilmeden: resmin tamamı görünür (object-contain), kutuda kalan boşluk aynı resmin bulanık haliyle dolar.
    Kart boyutları değişmez. Kapsayan kutu "relative overflow-hidden" olmalı.
    Kullanım: <x-cover-fit :src="$url" :alt="$name" class="h-full w-full transition group-hover:scale-105" loading="lazy" />
--}}
@props(['src', 'alt' => ''])

@php $positioned = str_contains((string) $attributes->get('class'), 'absolute'); @endphp

<img src="{{ $src }}" alt="" aria-hidden="true" loading="lazy" decoding="async"
    class="pointer-events-none absolute inset-0 h-full w-full scale-110 object-cover opacity-70 blur-xl">
<img src="{{ $src }}" alt="{{ $alt }}" {{ $attributes->class(['object-contain', 'relative' => !$positioned]) }}>
