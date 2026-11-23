<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\City;
use App\Models\CityGuide;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

/**
 * /llms.txt (llmstxt.org): yapay zekâ asistanlarına sitenin ne olduğunu ve önemli sayfaları anlatan düz metin
 * (Markdown). Şehir rehberleri ve yazılar veritabanından gelir; bir saat önbellekte tutulur.
 */
class LlmsController extends Controller
{
    public function index()
    {
        $text = Cache::remember('llms.txt', now()->addHour(), fn () => $this->build());

        return response($text, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
            'X-Robots-Tag' => 'noindex',
        ]);
    }

    private function url(string $path): string
    {
        return LaravelLocalization::getLocalizedURL('en', $path, [], true);
    }

    private static function line(string $text): string
    {
        return trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
    }

    private function build(): string
    {
        app()->setLocale('en');
        $out = [];
        $out[] = '# TripSpoiler';
        $out[] = '';
        $out[] = '> TripSpoiler is a free travel guide and activity finder. We write city guides and travel articles, and list tours, tickets and experiences that are booked on the official partner\'s website. When a visitor books through our links we may earn a commission; the price stays the same.';
        $out[] = '';
        $out[] = 'The site is available in ' . count(LaravelLocalization::getSupportedLocales()) . ' languages (' . implode(', ', array_keys(LaravelLocalization::getSupportedLocales())) . '); every page has hreflang alternates. Prices, opening hours and transport details are approximate; always check the partner or official source before travelling.';
        $out[] = '';
        $out[] = '## Main pages';
        $out[] = '';
        $out[] = '- [Things to do](' . $this->url(route('activities.index', [], false)) . '): tours, tickets and experiences by city';
        $out[] = '- [Travel blog](' . $this->url(route('blog.index', [], false)) . '): itineraries, tips and practical guides';
        $out[] = '- [Weather](' . $this->url(route('weather', [], false)) . ') and [local time](' . $this->url(route('time', [], false)) . ') for our cities';
        $out[] = '- [Exchange rates](' . $this->url(route('rates', [], false)) . ')';
        $out[] = '- [About](' . $this->url(route('about', [], false)) . '), [Contact](' . $this->url(route('contact', [], false)) . '), [Privacy and affiliate disclosure](' . $this->url(route('privacy', [], false)) . ')';

        $cityIds = CityController::guideCityIds();
        $cities = City::visible()->whereIn('id', $cityIds)->with('country')->orderBy('name')->get();
        if ($cities->isNotEmpty()) {
            $guides = CityGuide::whereIn('city_id', $cities->pluck('id'))->get()->keyBy('city_id');
            $out[] = '';
            $out[] = '## City guides';
            $out[] = '';
            foreach ($cities as $city) {
                $guide = $guides->get($city->id);
                $about = $guide ? Str::limit(self::line((string) $guide->description), 200) : '';
                $facts = collect($guide?->facts() ?? [])->take(4)->map(fn ($fact) => $fact['label'] . ': ' . self::line($fact['value']))->implode('; ');
                $out[] = '- [' . $city->name . ($city->country ? ', ' . $city->country->name : '') . ' travel guide](' . $this->url(route('cities.show', ['slug' => $city->slug], false)) . ')'
                    . ($about !== '' ? ': ' . $about : '') . ($facts !== '' ? ' Quick facts — ' . $facts . '.' : '');
            }
        }

        $places = \App\Models\Attraction::visible()->with('city')->orderBy('city_id')->orderBy('sort_order')->get();
        if ($places->isNotEmpty()) {
            $out[] = '';
            $out[] = '## Places to visit (tickets, opening hours, how to get there)';
            $out[] = '';
            foreach ($places as $place) {
                $out[] = '- [' . self::line((string) $place->getTranslation('name', 'en', false)) . ', ' . $place->city?->name . '](' . $this->url(route('attractions.show', ['slug' => $place->slug], false)) . ')'
                    . (($s = self::line((string) $place->getTranslation('summary', 'en', false))) !== '' ? ': ' . \Illuminate\Support\Str::limit($s, 180) : '');
            }
        }

        $posts = Blog::published()->whereNotNull('slug')->orderByDesc('published_at')->limit(150)->get();
        if ($posts->isNotEmpty()) {
            $out[] = '';
            $out[] = '## Travel articles';
            $out[] = '';
            foreach ($posts as $post) {
                $title = self::line((string) $post->getTranslation('title', 'en', false));
                if ($title === '') {
                    continue;
                }
                $excerpt = Str::limit(self::line((string) $post->getTranslation('excerpt', 'en', false)), 180);
                $out[] = '- [' . $title . '](' . $this->url(route('blog.show', ['slug' => $post->slug], false)) . ')' . ($excerpt !== '' ? ': ' . $excerpt : '');
            }
        }

        $out[] = '';
        $out[] = '## Optional';
        $out[] = '';
        $out[] = '- [Sitemap](' . url('/sitemap.xml') . '): every page in every language';
        $out[] = '- [MCP server](https://admin.tripspoiler.com/mcp/site): read-only search of tours, tickets and travel guides (no login; card: ' . url('/.well-known/mcp/server-card.json') . ')';

        return implode("\n", $out) . "\n";
    }
}
