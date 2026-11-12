<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Blog;
use App\Models\City;
use DateTimeInterface;
use Illuminate\Support\Facades\Cache;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

/**
 * sitemap.xml: her sayfa her dilde ayrı adres, her adreste diğer dillerin hreflang karşılıkları
 * (Google çok dilli siteler için önerir). Yönlendiren adresler (ör. /cities → /cities/{şehir}) eklenmez.
 * Bir saat önbellekte tutulur; yeni içerik en geç bir saat içinde görünür.
 */
class SitemapController extends Controller
{
    public function index()
    {
        $xml = Cache::remember('sitemap.xml', now()->addHour(), fn () => $this->build()->render());

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
            'X-Robots-Tag' => 'noindex',
        ]);
    }

    private function build(): Sitemap
    {
        $sitemap = Sitemap::create();
        $locales = array_keys(LaravelLocalization::getSupportedLocales());

        $add = function (string $path, float $priority, string $frequency, ?DateTimeInterface $modified = null) use ($sitemap, $locales) {
            $urls = collect($locales)->mapWithKeys(fn ($locale) => [$locale => LaravelLocalization::getLocalizedURL($locale, $path, [], true)]);

            foreach ($urls as $url) {
                $tag = Url::create($url)->setPriority($priority)->setChangeFrequency($frequency);
                if ($modified) {
                    $tag->setLastModificationDate($modified);
                }
                foreach ($urls as $alternateLocale => $alternateUrl) {
                    $tag->addAlternate($alternateUrl, $alternateLocale);
                }
                $tag->addAlternate($urls['en'] ?? $urls->first(), 'x-default');
                $sitemap->add($tag);
            }
        };

        // Sabit sayfalar
        $add(route('home', [], false), 1.0, Url::CHANGE_FREQUENCY_DAILY);
        $add(route('activities.index', [], false), 0.8, Url::CHANGE_FREQUENCY_WEEKLY);
        $add(route('blog.index', [], false), 0.7, Url::CHANGE_FREQUENCY_WEEKLY);
        $add(route('reviews.index', [], false), 0.5, Url::CHANGE_FREQUENCY_WEEKLY);
        $add(route('about', [], false), 0.5, Url::CHANGE_FREQUENCY_MONTHLY);
        $add(route('contact', [], false), 0.4, Url::CHANGE_FREQUENCY_YEARLY);
        $add(route('privacy', [], false), 0.3, Url::CHANGE_FREQUENCY_YEARLY);
        $add(route('rates', [], false), 0.4, Url::CHANGE_FREQUENCY_DAILY);
        $add(route('weather', [], false), 0.5, Url::CHANGE_FREQUENCY_DAILY);
        if (\App\Models\Campaign::liveCount() > 0) {
            $add(route('campaigns', [], false), 0.6, Url::CHANGE_FREQUENCY_WEEKLY);
        }

        foreach (City::visible()->whereNotNull('slug')->get(['id', 'slug', 'updated_at']) as $city) {
            $add(route('cities.show', ['slug' => $city->slug], false), 0.7, Url::CHANGE_FREQUENCY_MONTHLY, $city->updated_at);
        }

        foreach (Blog::published()->whereNotNull('slug')->get(['id', 'slug', 'updated_at', 'published_at']) as $post) {
            $add(route('blog.show', ['slug' => $post->slug], false), 0.6, Url::CHANGE_FREQUENCY_MONTHLY, $post->updated_at ?? $post->published_at);
        }

        foreach (Activity::visible()->whereNotNull('slug')->get(['id', 'slug', 'updated_at']) as $activity) {
            $add(route('activities.show', ['slug' => $activity->slug], false), 0.7, Url::CHANGE_FREQUENCY_WEEKLY, $activity->updated_at);
        }

        return $sitemap;
    }
}
