<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\City;
use App\Models\Activity;

class ActivityController extends Controller
{
    public function index(Request $request)
    {
        // Sadece aktivitesi olan şehirler (boş şehir seçenek olarak çıkmasın)
        $cities = City::visible()
            ->with('country')
            ->whereHas('activities', fn ($q) => $q->where('active', true))
            ->withCount(['activities' => fn ($q) => $q->where('active', true)])
            ->orderByDesc('population')
            ->get();

        $requested = $request->get('city');
        $city = ($requested ? $cities->firstWhere('slug', $requested) : null)
            ?? $cities->firstWhere('id', (int) session('selected_city_id'))
            ?? $cities->first();

        // Hiçbir şehirde aktivite yoksa da bir şehir seçili olsun: başlık şehirsiz yarım kalmasın ("Things to do in …"),
        // sayfa "bu şehirde henüz aktivite yok" der
        $city ??= City::visible()->with('country')
            ->when($requested, fn ($q) => $q->where('slug', $requested))
            ->orderByDesc('population')->first()
            ?? City::visible()->with('country')->orderByDesc('population')->first();

        if ($city && $cities->contains('id', $city->id)) {
            session(['selected_city_id' => $city->id]);
        }

        $activities = $city
            ? Activity::visible()->where('city_id', $city->id)->with(['cover', 'city'])->orderBy('sort_order')->orderBy('id')->get()
            : collect();

        // Banner resmi (panel: Sayfalar › Aktiviteler › Banner resimleri)
        $banner = \App\Models\PageBanner::urls('activities', $city?->id);

        return view('activities.index', [
            'banner' => $banner['desktop'],
            'bannerMobile' => $banner['mobile'],
            'bannerFocus' => $banner['focus'],
            'cities' => $cities,
            'city' => $city,
            'activities' => $activities,
            // listenin sonunda "Aradığını bulamadın mı?" kartları (partnerin şehir sayfasına)
            'moreLinks' => \App\Models\PartnerLink::forCity($city?->id),
            'stats' => [
                'total' => $activities->count(),
                'passes' => $activities->where('type', 'pass')->count(),
                'audio' => $activities->where('audio_guide', true)->count(),
                'languages' => count(\Mcamara\LaravelLocalization\Facades\LaravelLocalization::getSupportedLocales()),
            ],
        ]);
    }

    public function show(string $slug)
    {
        $locale = app()->getLocale();

        $activity = Activity::firstBySlugOrFail(Activity::visible()->with(['images', 'city.country', 'partner']), $slug);

        // Bu dilin adresi başka: kalıcı yönlendirme
        if ($activity->localSlug() !== $slug) {
            return redirect()->to(route('activities.show', $activity->localSlug()) . (request()->getQueryString() ? '?' . request()->getQueryString() : ''), 301);
        }
        \App\Support\LocalUrl::current('activities.show', $activity);

        $name = $activity->name;
        $metaTitle = $activity->getTranslation('meta_title', $locale) ?: $name . ' | TripSpoiler';
        $metaDescription = $activity->getTranslation('meta_description', $locale) ?: 'Learn about ' . $name . '. What it includes, how it works, and whether it makes sense for your trip.';

        // Aynı şehirdeki diğer deneyimler ("Bunlar da ilgini çekebilir")
        $related = Activity::visible()->with(['images', 'city'])
            ->where('city_id', $activity->city_id)->whereKeyNot($activity->id)
            ->orderBy('sort_order')->orderBy('id')->limit(4)->get();

        return view('activities.show', compact(
            'activity',
            'related',
            'locale',
            'metaTitle',
            'metaDescription',
        ));
    }
}
