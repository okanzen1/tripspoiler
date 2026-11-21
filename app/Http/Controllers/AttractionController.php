<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Attraction;
use App\Support\Schema;
use Illuminate\Support\Str;

/**
 * Yer sayfası: /{dil}/attractions/{slug}. O yerin biletleri (passler ve kombolar önce), ziyaret bilgileri, SSS,
 * aynı şehrin diğer yerleri. Google: TouristAttraction + bilet listesi.
 */
class AttractionController extends Controller
{
    public function show(string $slug)
    {
        $attraction = Attraction::visible()->with('city.country')->where('slug', $slug)->firstOrFail();
        \App\Support\LocalUrl::current('attractions.show', $attraction);

        $order = ['pass' => 0, 'package' => 1, 'product' => 2];
        $tickets = $attraction->activities()->visible()->with(['images', 'partner'])->get()
            ->sortBy([fn ($a, $b) => ($order[$a->type] ?? 3) <=> ($order[$b->type] ?? 3), fn ($a, $b) => $a->sort_order <=> $b->sort_order])->values();

        $others = Attraction::visible()->where('city_id', $attraction->city_id)->whereKeyNot($attraction->id)
            ->orderBy('sort_order')->orderBy('id')->limit(6)->get();

        $url = Schema::canonical();
        $schema = array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'TouristAttraction',
            '@id' => $url . '#attraction',
            'name' => $attraction->name,
            'description' => Str::limit(trim(strip_tags((string) ($attraction->summary ?: $attraction->content))), 300),
            'url' => $url,
            'image' => $attraction->imageUrl(),
            'sameAs' => $attraction->official_url ?: null,
            'containedInPlace' => $attraction->city ? ['@type' => 'City', 'name' => $attraction->city->name] : null,
            'publicAccess' => true,
            'subjectOf' => ['@type' => 'WebPage', '@id' => $url, 'isPartOf' => ['@id' => Schema::websiteId()], 'publisher' => Schema::organizationRef()],
        ], fn ($v) => $v !== null && $v !== '');

        return view('attractions.show', [
            'attraction' => $attraction,
            'tickets' => $tickets,
            'others' => $others,
            // altta "… gezi rehberleri": bu yerin turlarına bağlı ve aynı şehrin yazıları
            'relatedPosts' => \App\Support\RelatedPosts::for($attraction->city_id, $tickets->pluck('id')->all()),
            'schema' => $schema,
            'itemList' => $tickets->isNotEmpty() ? Schema::itemList($tickets->map(fn (Activity $a) => [$a->name, route('activities.show', $a->localSlug())])) : null,
            'metaTitle' => $attraction->seoTitle(),
            'metaDescription' => Str::limit(trim((string) ($attraction->meta_description ?: strip_tags((string) $attraction->summary))), 155),
        ]);
    }
}
