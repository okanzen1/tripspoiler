<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\CityGuide;
use App\Models\CityGuideSection;
use Illuminate\Support\Str;

class CityController extends Controller
{
    /**
     * Seçili şehir (aktiviteler sayfasıyla ortak: session "selected_city_id"), yoksa en kalabalık şehir.
     */
    public function index()
    {
        $withGuide = City::visible()->whereIn('id', self::guideCityIds());
        $city = (clone $withGuide)->find(session('selected_city_id'));

        // Seçili şehrin rehberi yoksa rehberi olan ilk şehir; ama kullanıcının seçimi (aktivitelerdeki şehir) değişmez
        if (!$city) {
            $city = (clone $withGuide)->orderByDesc('population')->first()
                ?? City::visible()->orderByDesc('population')->first();

            if (!$city) {
                // Veritabanında henüz şehir yok (sıfır kurulum): ana sayfaya yönlendir
                return redirect()->route('home');
            }

            return redirect()->route('cities.show', ['slug' => $city->slug])->with('keep_selected_city', true);
        }

        return redirect()->route('cities.show', ['slug' => $city->slug]);
    }

    /**
     * Fotoğrafın üstündeki iki kart (panel: Şehir rehberi › şehir › Banner'daki kartlar).
     * hero_cards "sol,sağ": boş = otomatik (kapak resmi olan sıradaki konu), 0 = gösterme, sayı = o bölüm.
     *
     * @return list<CityGuideSection|null>  [sol alt, sağ üst]
     */
    private static function floatingCards(?CityGuide $guide, \Illuminate\Support\Collection $nav): array
    {
        $values = array_pad(explode(',', (string) $guide?->hero_cards), 2, '');
        $picked = collect($values)->map(fn ($v) => $v !== '' && $v !== '0' ? $nav->firstWhere('id', (int) $v) : null);
        $auto = $nav->filter(fn ($s) => $s->image_path)->reject(fn ($s) => $picked->contains(fn ($p) => $p?->is($s)))->values();

        $i = 0;

        return collect($values)->map(function ($v, $slot) use ($picked, $auto, &$i) {
            if ($v === '0') {
                return null;
            }

            return $picked[$slot] ?? $auto->get($i++);
        })->all();
    }

    /**
     * Rehberinde en az bir yazılmış ve açık alan olan şehirler (seçicide sadece bunlar çıkar).
     *
     * @return list<int>
     */
    public static function guideCityIds(): array
    {
        return CityGuideSection::where('active', true)->with('topic')->get()
            ->filter(fn (CityGuideSection $section) => $section->topic && $section->isWritten())
            ->pluck('city_id')->unique()->values()->all();
    }

    /**
     * Şehir sayfası: banner (genel giriş), rehber alanları ve panelde açılan diğer bölümler.
     * Rehber alanlarının sırası Sayfalar › Şehirler › Bölümler'den (PageSections "guide:{id}").
     */
    public function show(string $slug)
    {
        $city = City::visible()
            ->where('slug', $slug)
            ->with('country')
            ->withCount([
                'activities' => fn ($q) => $q->where('active', true),
                'blogs' => fn ($q) => $q->published(),
            ])
            ->firstOrFail();

        // Aktiviteler sayfası da bu şehirle açılır (rehberi olmayan şehirden buraya yönlendirildiysek seçim korunur)
        if (!session('keep_selected_city')) {
            session(['selected_city_id' => $city->id]);
        }

        // Seçicide sadece rehberi olan şehirler
        $cities = City::visible()
            ->whereIn('id', self::guideCityIds())
            ->with('country')
            ->withCount(['activities' => fn ($q) => $q->where('active', true)])
            ->orderByDesc('activities_count')
            ->orderByDesc('population')
            ->get();

        $guide = CityGuide::where('city_id', $city->id)->first();

        // Bu şehirde açık ve yazılmış alanlar: topic id → bölüm
        $guideSections = CityGuideSection::where('city_id', $city->id)
            ->where('active', true)
            ->with('topic')
            ->get()
            ->filter(fn (CityGuideSection $section) => $section->topic && $section->isWritten())
            ->keyBy('topic_id');

        // Sayfadaki sırayla (rehber menüsü için)
        $guideNav = collect(\App\Support\PageSections::content('cities'))
            ->filter(fn ($key) => str_starts_with($key, 'guide:'))
            ->map(fn ($key) => $guideSections->get((int) substr($key, 6)))
            ->filter()
            ->values();

        // Banner (panel: Sayfalar › Şehirler › Banner resimleri)
        ['desktop' => $banner, 'mobile' => $bannerMobile, 'focus' => $bannerFocus] = \App\Models\PageBanner::urls('cities', $city->id);

        // Hero: şehrin rehber girişi; yoksa panelin varsayılan yazıları (Sayfalar › Şehirler › Hero ayarları), o da yoksa dil dosyası
        $hero = \App\Support\PageHero::texts('cities', [
            'eyebrow' => __('cities-page.eyebrow'),
            'title' => __('cities-page.default_title', ['city' => ':city']),
            'description' => __('cities-page.default_description', ['city' => ':city']),
        ], $city->name);
        $title = $guide?->title ?: $hero['title'];
        $description = $guide?->description ?: $hero['description'];

        $citySchema = [
            '@context' => 'https://schema.org',
            '@type' => 'TouristDestination',
            '@id' => url()->current() . '#destination',
            'name' => $city->name,
            'description' => Str::limit(strip_tags($description), 300),
            'url' => url()->current(),
            'image' => $banner,
            'touristType' => 'Tourists',
            'isPartOf' => ['@id' => config('app.url') . '#website'],
            'provider' => ['@id' => config('app.url') . '#organization'],
            'includesAttraction' => $guideNav->map(fn (CityGuideSection $section) => ['@type' => 'TouristAttraction', 'name' => strip_tags($section->title), 'url' => url()->current() . '#' . $section->topic->key])->all(),
        ];

        return view('cities.show', [
            'city' => $city,
            'cities' => $cities,
            'banner' => $banner,
            'bannerMobile' => $bannerMobile,
            'eyebrow' => $hero['eyebrow'],
            'floating' => self::floatingCards($guide, $guideNav),
            'look' => \App\Support\PageHero::visual('cities', $bannerFocus),
            'title' => $title,
            'description' => $description,
            'guideSections' => $guideSections,
            'guideNav' => $guideNav,
            'metaTitle' => __('cities-page.meta_title', ['city' => $city->name]),
            'metaDescription' => Str::limit(strip_tags($description), 155),
            'citySchema' => $citySchema,
        ]);
    }
}
