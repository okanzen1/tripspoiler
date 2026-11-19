<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Support\Popularity;
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
        $picks = Setting::many(['hero_featured']);

        $reviews = Review::approved()->whereNull('activity_id');

        return [
            'banner' => $this->banner(),
            'texts' => \App\Models\HeroText::forLocale(app()->getLocale()),
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
     * Hero'nun sağındaki dönen "Şu an popüler" kartı: önce panelde seçilenler (o sırayla, en fazla 5),
     * ardından listede olmayan her şehrin en popüler ürünü (App\Support\Popularity). Görseli olmayan atlanır.
     */
    public const FEATURED_MAX_CITIES = 20;

    private function featured(?string $picked)
    {
        $ids = array_values(array_filter(array_map('intval', explode(',', (string) $picked))));
        $query = Activity::visible()->whereHas('cover')->with(['cover', 'city']);

        $chosen = $ids
            ? (clone $query)->whereIn('id', $ids)->get()->sortBy(fn ($activity) => array_search($activity->id, $ids))->values()
            : collect();

        $auto = Popularity::mix(
            Popularity::rank((clone $query)->whereNotIn('city_id', $chosen->pluck('city_id')->unique()->all() ?: [0]))->get(),
            self::FEATURED_MAX_CITIES, 1,
        );

        return $chosen->concat($auto)->values();
    }

    /**
     * Hero arka planı: panel > Yönetim > Site ayarları. Yüklenmemişse public/images/hero-banner.webp.
     */
    private function banner(): array
    {
        $settings = Setting::many(['hero_image', 'hero_image_mobile', 'hero_focus', 'hero_focus_mobile', 'hero_overlay']);
        $focus = max(0, min(100, (int) ($settings['hero_focus'] ?? 50)));
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
            'focus' => $focus,
            // Telefonun kendi kadrajı; ayarlanmamışsa telefon görseli varken ortası, yoksa masaüstününki
            'focus_mobile' => max(0, min(100, (int) ($settings['hero_focus_mobile'] ?? ($settings['hero_image_mobile'] ? 50 : $focus)))),
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
