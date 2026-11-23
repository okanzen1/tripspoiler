<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Blog;
use App\Models\City;
use DateTimeInterface;
use Illuminate\Support\Facades\Cache;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\SitemapIndex;
use Spatie\Sitemap\Tags\Url;

/**
 * sitemap.xml = site haritası DİZİNİ: her dil ve içerik türü için ayrı, küçük bir harita (/sitemaps/en-blog.xml…).
 * Tek dosya 14 MB'ı bulmuştu (her sayfa her dilde + karşılıkları); arama motorları ve tarayıcılar küçük dosyaları
 * hızlı okur. Her adreste diğer dillerin hreflang karşılıkları durur (Google çok dilli siteler için önerir).
 * Yönlendiren adresler eklenmez. Her harita bir saat önbellekte tutulur.
 */
class SitemapController extends Controller
{
    public const TYPES = ['pages', 'cities', 'attractions', 'blog', 'activities'];

    public function index()
    {
        $xml = Cache::remember('sitemap-index.xml', now()->addHour(), function () {
            $index = SitemapIndex::create();
            foreach ($this->locales() as $locale) {
                foreach (self::TYPES as $type) {
                    $index->add(url("/sitemaps/{$locale}-{$type}.xml"));
                }
            }

            return $index->render();
        });

        return $this->xml($xml);
    }

    public function show(string $locale, string $type)
    {
        abort_unless(in_array($locale, $this->locales(), true) && in_array($type, self::TYPES, true), 404);

        $xml = Cache::remember("sitemap:{$locale}:{$type}", now()->addHour(), fn () => $this->build($locale, $type)->render());

        return $this->xml($xml);
    }

    private function xml(string $xml)
    {
        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
            'X-Robots-Tag' => 'noindex',
        ]);
    }

    /** @return list<string> */
    private function locales(): array
    {
        return array_keys(LaravelLocalization::getSupportedLocales());
    }

    private function build(string $only, string $type): Sitemap
    {
        $sitemap = Sitemap::create();
        $locales = $this->locales();

        // $images: Google Görseller için sayfadaki resimler (mutlak adres)
        // $path: adres ya da dile göre adres veren fonksiyon (yazı / aktivite: her dilin kendi slug'ı)
        // Bu haritaya yalnızca $only dilinin adresi yazılır; diğer diller hreflang karşılığı olarak eklenir.
        $add = function (string|\Closure $path, float $priority, string $frequency, ?DateTimeInterface $modified = null, array $images = []) use ($sitemap, $locales, $only) {
            $urls = collect($locales)->mapWithKeys(fn ($locale) => [$locale => LaravelLocalization::getLocalizedURL($locale, $path instanceof \Closure ? $path($locale) : $path, [], true)]);

            $tag = Url::create($urls[$only])->setPriority($priority)->setChangeFrequency($frequency);
            if ($modified) {
                $tag->setLastModificationDate($modified);
            }
            foreach ($urls as $alternateLocale => $alternateUrl) {
                $tag->addAlternate($alternateUrl, $alternateLocale);
            }
            $tag->addAlternate($urls['en'] ?? $urls->first(), 'x-default');
            foreach (array_filter($images) as $image) {
                $tag->addImage(url($image));
            }
            $sitemap->add($tag);
        };

        match ($type) {
            'pages' => $this->pages($add),
            'cities' => $this->cities($add),
            'attractions' => $this->attractions($add),
            'blog' => $this->blog($add),
            'activities' => $this->activities($add),
        };

        return $sitemap;
    }

    private function pages(\Closure $add): void
    {
        $add(route('home', [], false), 1.0, Url::CHANGE_FREQUENCY_DAILY);
        $add(route('activities.index', [], false), 0.8, Url::CHANGE_FREQUENCY_WEEKLY);
        $add(route('blog.index', [], false), 0.7, Url::CHANGE_FREQUENCY_WEEKLY);
        $add(route('reviews.index', [], false), 0.5, Url::CHANGE_FREQUENCY_WEEKLY);
        $add(route('about', [], false), 0.5, Url::CHANGE_FREQUENCY_MONTHLY);
        $add(route('contact', [], false), 0.4, Url::CHANGE_FREQUENCY_YEARLY);
        if (\App\Support\PlanTrip::available()) {
            $add(route('plan', [], false), 0.6, Url::CHANGE_FREQUENCY_MONTHLY);
        }
        $add(route('privacy', [], false), 0.3, Url::CHANGE_FREQUENCY_YEARLY);
        $add(route('rates', [], false), 0.4, Url::CHANGE_FREQUENCY_DAILY);
        $add(route('weather', [], false), 0.5, Url::CHANGE_FREQUENCY_DAILY);
        $add(route('time', [], false), 0.5, Url::CHANGE_FREQUENCY_MONTHLY);
        if (\App\Models\Campaign::liveCount() > 0) {
            $add(route('campaigns', [], false), 0.6, Url::CHANGE_FREQUENCY_WEEKLY);
        }
    }

    private function cities(\Closure $add): void
    {
        foreach (City::visible()->whereNotNull('slug')->get(['id', 'slug', 'latitude', 'updated_at']) as $city) {
            $add(route('cities.show', ['slug' => $city->slug], false), 0.7, Url::CHANGE_FREQUENCY_MONTHLY, $city->updated_at);
            // Şehre özel hava durumu ve yerel saat sayfaları (adresteki ?city= ile ayrı sayfa, kendi canonical'ı var)
            if ($city->latitude !== null) {
                $add(route('weather', ['city' => $city->slug], false), 0.5, Url::CHANGE_FREQUENCY_DAILY);
            }
            $add(route('time', ['city' => $city->slug], false), 0.4, Url::CHANGE_FREQUENCY_MONTHLY);
        }
    }

    private function attractions(\Closure $add): void
    {
        foreach (\App\Models\Attraction::visible()->get(['id', 'slug', 'image_path', 'updated_at']) as $place) {
            $add(route('attractions.show', ['slug' => $place->slug], false), 0.8, Url::CHANGE_FREQUENCY_WEEKLY, $place->updated_at, [$place->imageUrl()]);
        }
    }

    private function blog(\Closure $add): void
    {
        foreach (Blog::published()->whereNotNull('slug')->get() as $post) {
            $add(fn ($locale) => route('blog.show', ['slug' => $post->localSlug($locale)], false), 0.6, Url::CHANGE_FREQUENCY_MONTHLY, $post->updated_at ?? $post->published_at, [$post->coverUrl()]);
        }
    }

    private function activities(\Closure $add): void
    {
        foreach (Activity::visible()->with('images')->whereNotNull('slug')->get() as $activity) {
            $add(fn ($locale) => route('activities.show', ['slug' => $activity->localSlug($locale)], false), 0.7, Url::CHANGE_FREQUENCY_WEEKLY, $activity->updated_at,
                $activity->images->take(10)->map(fn ($image) => $image->url())->all());
        }
    }
}
