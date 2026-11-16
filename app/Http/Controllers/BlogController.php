<?php

namespace App\Http\Controllers;

use App\Models\City;
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

        // Yazının altında gösterilecek diğer rehberler: önce aynı şehirdekiler, sonra diğerleri
        $related = Blog::published()
            ->where('id', '!=', $blog->id)
            ->with('city')
            ->orderByRaw('CASE WHEN city_id = ? THEN 0 ELSE 1 END', [$blog->city_id])
            ->latest('published_at')
            ->latest('id')
            ->take(3)
            ->get();

        return view('blog.show', compact('blog', 'locale', 'related'));
    }
}
