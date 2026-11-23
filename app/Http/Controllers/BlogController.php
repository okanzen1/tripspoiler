<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Activity;
use App\Models\Blog;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    /**
     * Blog: seçili şehrin yazıları (şehir seçimi aktiviteler ve şehirler sayfasıyla ortak: session "selected_city_id").
     * En yeni yazı banner'da öne çıkar; diğerleri etiket ve aramayla süzülen kartlar.
     */
    public function index(Request $request)
    {
        // Seçicide sadece yayında yazısı olan şehirler; genel yazı (şehirsiz) varsa her şehirde okunacak yazı vardır
        $generalCount = Blog::published()->doesntHave('cities')->count();
        $hasGeneral = $generalCount > 0;
        $cities = City::visible()
            ->with('country')
            ->withCount(['blogs' => fn ($q) => $q->published(), 'activities' => fn ($q) => $q->where('active', true)])
            ->unless($hasGeneral, fn ($q) => $q->whereHas('blogs', fn ($q) => $q->published()))
            ->orderByDesc('blogs_count')
            ->orderByDesc('population')
            ->get()
            // seçicideki yazı sayısı genel yazıları da sayar
            ->each(fn (City $c) => $c->blogs_count += $generalCount);

        $cityId = (int) ($request->get('city_id') ?? session('selected_city_id'));
        $city = $cities->firstWhere('id', $cityId);

        // Yazısı olan bir şehir seçildiyse seçim ortak olur; yoksa ilk şehrin yazıları gösterilir, seçim değişmez
        if ($city) {
            session(['selected_city_id' => $city->id]);
        } else {
            $city = $cities->first();
        }

        // seçili şehrin yazıları + genel yazılar (şehir yoksa yalnızca genel yazılar)
        $blogs = Blog::published()->with('city')->forCity($city?->id)
            ->orderByRaw('sort_order = 0 desc')->orderBy('sort_order')->latest('published_at')->latest('id')->get();

        // En çok geçen etiketler (filtre çipleri)
        $tags = $blogs->flatMap(fn (Blog $blog) => array_filter((array) $blog->tags))
            ->countBy()->sortDesc()->keys()->take(8)->values();

        return view('blog.index', [
            'cities' => $cities,
            'city' => $city,
            'blogs' => $blogs,
            'featured' => $blogs->first(),
            // banner'da öne çıkan yazının yanında sonraki iki yazı
            'side' => $blogs->slice(1, 2)->values(),
            // o şehrin aktiviteleri / rehberi varsa banner'da düğmesi çıkar
            'hasActivities' => (bool) $city?->activities_count,
            'hasGuide' => $city && in_array($city->id, CityController::guideCityIds(), true),
            'tags' => $tags,
            'cityId' => $city?->id,
        ]);
    }

    public function show(string $slug)
    {
        $locale = app()->getLocale();

        $blog = Blog::firstBySlugOrFail(Blog::published()->with([
            'city.country',
            'activities' => fn ($q) => $q->visible()->with(['city.country', 'cover']),
        ]), $slug);

        // Bu dilin adresi başka (ör. /de/blog/english-slug → /de/blog/deutscher-slug): kalıcı yönlendirme
        if ($blog->localSlug() !== $slug) {
            return redirect()->to(route('blog.show', $blog->localSlug()) . (request()->getQueryString() ? '?' . request()->getQueryString() : ''), 301);
        }
        \App\Support\LocalUrl::current('blog.show', $blog);

        // Panelde aktivite bağlanmamışsa kendiliğinden: yazıda geçen yerlerin biletleri, sonra şehrin popülerleri
        if ($blog->activities->isEmpty()) {
            $blog->setRelation('activities', $this->suggestedActivities($blog));
        }

        // Yazının altında gösterilecek diğer rehberler: önce aynı şehirdekiler, sonra diğerleri
        $related = Blog::published()
            ->where('id', '!=', $blog->id)
            ->with('city')
            ->orderByRaw('CASE WHEN city_id = ? THEN 0 ELSE 1 END', [$blog->city_id])
            ->latest('published_at')
            ->latest('id')
            ->take(3)
            ->get();

        $content = $this->linkedContent($blog, $locale);

        return view('blog.show', compact('blog', 'locale', 'related', 'content'));
    }

    /**
     * Aktivite bağlanmamış yazı için en fazla 3 aktivite: önce yazıda adı geçen yerlerin (Ayasofya, Kolezyum…) biletleri,
     * kalan yer yazının şehrinin popüler aktiviteleriyle dolar. Şehri olmayan genel yazıda sadece adı geçen yerlerinkiler.
     * Arama motorundan bloga gelen okur bir sonraki adımda bilete gidebilsin. Seçim bir gün önbellekte.
     */
    private function suggestedActivities(Blog $blog): \Illuminate\Support\Collection
    {
        $cityIds = $blog->cities()->pluck('cities.id')->push($blog->city_id)->filter()->unique()->values()->all();

        $ids = \Illuminate\Support\Facades\Cache::remember('blog-picks:' . $blog->id . ':' . md5((string) $blog->updated_at), now()->addDay(), function () use ($blog, $cityIds) {
            $text = mb_strtolower(strip_tags((string) $blog->getTranslation('title', 'en', false) . ' ' . $blog->getTranslation('content', 'en', false)));
            $mentioned = \App\Models\Attraction::visible()
                ->when($cityIds, fn ($q) => $q->whereIn('city_id', $cityIds))
                ->get(['id', 'name'])
                ->filter(function ($place) use ($text) {
                    $name = mb_strtolower(trim((string) $place->getTranslation('name', 'en', false)));

                    return mb_strlen($name) >= 4 && str_contains($text, $name);
                })
                ->pluck('id');

            $picks = $mentioned->isEmpty() ? collect() : Activity::visible()
                ->whereHas('attractions', fn ($q) => $q->whereIn('attractions.id', $mentioned))
                ->orderByDesc('is_popular')->orderBy('sort_order')->limit(3)->pluck('id');

            if ($picks->count() < 3 && $cityIds) {
                $picks = $picks->concat(Activity::visible()->whereIn('city_id', $cityIds)->whereNotIn('id', $picks)
                    ->orderByDesc('is_popular')->orderBy('sort_order')->orderBy('id')->limit(3 - $picks->count())->pluck('id'));
            }

            return $picks->values()->all();
        });

        if (!$ids) {
            return collect();
        }

        return Activity::visible()->with(['city.country', 'cover'])->whereIn('id', $ids)->get()
            ->sortBy(fn ($activity) => array_search($activity->id, $ids, true))->values();
    }

    /**
     * Yazı metni, içinde geçen yer adları o yerin sayfasına bağlanmış olarak (App\Support\AutoLinks).
     * Yazının şehir(ler)inin yerleri; şehri olmayan genel yazıda bütün yerler. Yazı ya da yerler değişince yenilenir.
     */
    private function linkedContent(Blog $blog, string $locale): string
    {
        $html = (string) $blog->content;
        $cityIds = $blog->cities()->pluck('cities.id')->push($blog->city_id)->filter()->unique()->values()->all();
        $places = \App\Models\Attraction::visible()->when($cityIds, fn ($q) => $q->whereIn('city_id', $cityIds));
        $stamp = (clone $places)->max('updated_at') . '|' . (clone $places)->count();

        return \Illuminate\Support\Facades\Cache::remember(
            'blog-links:' . $blog->id . ':' . $locale . ':' . md5($blog->updated_at . '|' . $stamp),
            now()->addDay(),
            function () use ($html, $places, $locale) {
                $targets = [];
                foreach ($places->get(['id', 'slug', 'name']) as $place) {
                    $url = route('attractions.show', ['slug' => $place->localSlug()]);
                    foreach (array_unique(array_filter([$place->getTranslation('name', $locale, false), $place->getTranslation('name', 'en', false)])) as $name) {
                        $targets[$name] ??= $url;
                    }
                }

                return \App\Support\AutoLinks::apply($html, $targets, $locale);
            }
        );
    }
}
