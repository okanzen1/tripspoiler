<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Blog;
use App\Models\City;
use App\Models\ActivityImage;
use App\Models\Review;
use App\Models\Setting;
use Illuminate\Http\Request;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

class HomeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('home', ['hero' => $this->hero(), 'features' => $this->features()]);
    }

    /**
     * Hero verisi: öne çıkan şehir kartı, son rehber ve rakamlar (hepsi panelden).
     */
    private function hero(): array
    {
        // Kart seçimleri: panel > Yönetim > Site ayarları > Anasayfa kartları. Boş olan otomatik dolar.
        $picks = Setting::many(['hero_featured', 'hero_pass_id', 'hero_guide_id']);

        $passes = Activity::visible()->where('type', 'pass');
        $pass = ($picks['hero_pass_id'] ? (clone $passes)->with('city')->find($picks['hero_pass_id']) : null)
            ?? (clone $passes)->with('city')->orderBy('sort_order')->orderBy('id')->first();

        $guide = ($picks['hero_guide_id'] ? Blog::published()->find($picks['hero_guide_id']) : null)
            ?? Blog::published()->latest('published_at')->first();

        $reviews = Review::approved()->whereNull('activity_id');

        return [
            'banner' => $this->banner(),
            'texts' => \App\Models\HeroText::forLocale(app()->getLocale()),
            'pass' => $pass,
            // "İstanbul için 5 şehir kartını karşılaştırın": seçilen kartın şehrindeki şehir kartları
            'passCount' => $pass ? (clone $passes)->where('city_id', $pass->city_id)->count() : 0,
            'guide' => $guide,
            'featured' => $this->featured($picks['hero_featured']),
            'cities' => City::visible()->orderByDesc('population')->get(['id', 'name', 'slug']),
            'stats' => [
                'experiences' => Activity::visible()->count(),
                'rating' => round((float) $reviews->avg('rating'), 1),
                'reviews' => $reviews->count(),
                'languages' => count(LaravelLocalization::getSupportedLocales()),
            ],
        ];
    }

    /**
     * Hero'nun sağındaki dönen "Şu an popüler" kartı: panelde seçilenler (o sırayla), yoksa popüler işaretliler.
     * Görünmeyen ya da görseli olmayan aktivite atlanır.
     */
    private function featured(?string $picked)
    {
        $ids = array_values(array_filter(array_map('intval', explode(',', (string) $picked))));
        $query = Activity::visible()->whereHas('cover')->with(['cover', 'city']);

        if ($ids) {
            $chosen = (clone $query)->whereIn('id', $ids)->get()
                ->sortBy(fn ($activity) => array_search($activity->id, $ids))
                ->values();

            if ($chosen->isNotEmpty()) {
                return $chosen;
            }
        }

        return $query->where('is_popular', true)->orderBy('sort_order')->orderBy('id')->limit(5)->get();
    }

    /**
     * Hero arka planı: panel > Yönetim > Site ayarları. Yüklenmemişse public/images/hero-banner.webp.
     */
    private function banner(): array
    {
        $settings = Setting::many(['hero_image', 'hero_image_mobile', 'hero_focus', 'hero_overlay']);
        $image = $settings['hero_image'];

        return [
            'src' => $image ? ActivityImage::mediaUrl($image) : asset('images/hero-banner.webp'),
            // Ekran genişliğine göre tarayıcı uygun boyutu seçer
            'srcset' => $image ? implode(', ', [
                ActivityImage::mediaUrl($image, 'sm') . ' 1280w',
                ActivityImage::mediaUrl($image, 'md') . ' 1920w',
                ActivityImage::mediaUrl($image) . ' 2560w',
            ]) : null,
            'mobile' => ActivityImage::mediaUrl($settings['hero_image_mobile']),
            'focus' => max(0, min(100, (int) ($settings['hero_focus'] ?? 50))),
            'overlay' => in_array($settings['hero_overlay'], ['soft', 'medium', 'strong'], true) ? $settings['hero_overlay'] : 'medium',
        ];
    }

    /**
     * Hero'nun altındaki kayan özellik kartları (panel > Site ayarları > Özellik kartları).
     */
    private function features()
    {
        return \App\Models\Feature::active()->orderBy('sort_order')->orderBy('id')->get()
            ->filter(fn ($feature) => filled($feature->title))->values();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
    }
}
