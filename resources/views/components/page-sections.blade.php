{{--
    Sayfanın alt bölümleri: panelde (Sayfalar › sayfa › Bölümler) verilen sırayla ve açık olanlar.
    Şehre özel bölümler (popüler, blog, şehirler çağrısı) verilen şehre göre gelir; o şehirde içerik yoksa görünmez.
    SSS: bu sayfaya yazılan sorular + seçili şehre yazılan sorular; hiç yoksa görünmez.
--}}
@props(['page', 'cityId' => null, 'reviewSource' => null, 'main' => null, 'activity' => null, 'post' => null, 'guideSections' => null])

@php
    // Aktivite detayında şehir = aktivitenin şehri
    // Blog yazısında şehir = yazının şehri
    $cityId = $cityId ?? $activity?->city_id ?? $post?->city_id;
    $colors = ['bg-white', 'bg-slate-50'];
    $i = 0;
    $next = function () use (&$i, $colors) { return $colors[$i++ % 2]; };
@endphp

@php
    // Şehir rehberi alanları ("guide:{topic id}"): sadece bu şehirde yazılmış ve açık olanlar, sayfadaki sırayla numaralanır
    $guideKeys = collect(\App\Support\PageSections::content($page))->filter(fn ($key) => str_starts_with($key, 'guide:') && $guideSections?->has((int) substr($key, 6)))->values();
@endphp

@foreach (\App\Support\PageSections::content($page) as $section)
    @if (str_starts_with($section, 'privacy_'))
        {{-- Gizlilik bölümleri sayfanın kendi düzeninde (içindekiler + numaralandırma) basılır; burada tekrar edilmez. --}}
        @continue
    @endif
    @if (view()->exists('sections.' . $section))
        {{-- sayfaya özel bölüm (ör. Hakkımızda: hikâye, nasıl çalışır…) --}}
        @include('sections.' . $section)
        @continue
    @endif
    @if (str_starts_with($section, 'guide:'))
        @if ($guideSections?->has((int) substr($section, 6)))
            <x-city-guide-section :section="$guideSections->get((int) substr($section, 6))" :color="$next()" :number="$guideKeys->search($section) + 1" :total="$guideKeys->count()" />
        @endif
        @continue
    @endif
    @switch($section)
        @case('main')
            {{-- sayfanın ana içeriği (ör. aktivite listesi): <x-slot:main> --}}
            {{ $main ?? '' }}
            @break
        @case('popular')
            @if ($activity)
                {{-- aktivite detayı: aynı şehirdeki diğer aktiviteler --}}
                <x-most-popular-activities source="similar" :source-id="$activity->id" :city-id="$activity->city_id" :exclude-id="$activity->id" :popular-only="false" limit="12" :color="$next()"
                    :heading="['eyebrow' => __('activity-show.similar_eyebrow'), 'title' => __('activity-show.similar_title'), 'highlight' => $activity->city?->name, 'desc' => __('activity-show.similar_desc')]" />
            @elseif ($cityId)
                <x-most-popular-activities source="city" :source-id="$cityId" :city-id="$cityId" limit="12" :color="$next()" />
            @endif
            @break
        @case('blog')
            @if ($activity)
                <x-most-popular-blog :city-id="$activity->city_id" :activity-id="$activity->id" />
            @elseif ($cityId)
                <x-most-popular-blog :city-id="$cityId" />
            @endif
            @break
        @case('city_entry')
            <x-city-entry-section :city-id="$cityId" />
            @break
        @case('faq')
            @if ($post)
                <x-faq source="blog" :source-id="$post->id" :bg-color="$next()" />
            @elseif ($activity)
                <x-faq source="activity" :source-id="$activity->id" :bg-color="$next()" />
            @else
                <x-faq source="page" :page="$page" :city-id="$cityId" :bg-color="$next()" />
            @endif
            @break
        @case('reviews')
            @if ($post)
                <x-reviews :source="'blog-show'" :source-id="$post->id" :sectionHeader="true" :reviewSummary="true" :testimonials="true" :color="$next()" />
            @else
                <x-reviews :source="$activity ? 'activity' : ($reviewSource ?? $page)" :source-id="$activity?->id" :color="$next()" />
            @endif
            @break
        @case('social')
            <x-social-presence-section :color="$next()" />
            @break
        @case('steps')
            <x-steps-section />
            @break
        @case('intro')
            <x-tripspoiler-intro />
            @break
    @endswitch
@endforeach
