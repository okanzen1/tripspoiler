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
        // Seçicide sadece yayında yazısı olan şehirler
        $cities = City::visible()
            ->with('country')
            ->withCount(['blogs' => fn ($q) => $q->published(), 'activities' => fn ($q) => $q->where('active', true)])
            ->whereHas('blogs', fn ($q) => $q->published())
            ->orderByDesc('blogs_count')
            ->orderByDesc('population')
            ->get();

        $cityId = (int) ($request->get('city_id') ?? session('selected_city_id'));
        $city = $cities->firstWhere('id', $cityId);

        // Yazısı olan bir şehir seçildiyse seçim ortak olur; yoksa ilk şehrin yazıları gösterilir, seçim değişmez
        if ($city) {
            session(['selected_city_id' => $city->id]);
        } else {
            $city = $cities->first();
        }

        $blogs = $city
            ? Blog::published()->with('city')->where('city_id', $city->id)
                ->orderByRaw('sort_order = 0 desc')->orderBy('sort_order')->latest('published_at')->latest('id')->get()
            : collect();

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

        $blog = Blog::published()
            ->where('slug', $slug)
            ->with([
                'activities' => fn ($q) => $q->visible()->with(['city.country', 'cover']),
            ])
            ->firstOrFail();

        /* ---------------- HERO TITLE ---------------- */

        $rawTitle = trim($blog->title ?? '');
        $words = preg_split('/\s+/', $rawTitle, -1, PREG_SPLIT_NO_EMPTY);
        $count = count($words);

        $first = '';
        $second = '';
        $break = false;

        if ($count === 1) {
            $first = $words[0];
        } elseif ($count === 2) {
            $first = $words[0];
            $second = $words[1];
        } elseif ($count === 3) {
            $first = $words[0] . ' ' . $words[1];
            $second = $words[2];
            $break = true;
        } else {
            $first = implode(' ', array_slice($words, 0, 3));
            $second = implode(' ', array_slice($words, 3));
            $break = true;
        }

        /* ---------------- HERO DATA ---------------- */

        $hero = [
            'title' => [
                'first'  => $first,
                'second' => $second,
                'break'  => $break,
            ],
            'themes'  => array_filter((array) ($blog->getTranslation('tags', $locale) ?: [])),
            'excerpt' => $blog->excerpt ?? '',
        ];

        return view('blog.show', compact('blog', 'locale', 'hero'));
    }
}
