<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use Illuminate\Support\Facades\Cache;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

/**
 * Blog RSS akışı (/{dil}/blog/feed): son 30 yazı, o dile çevrilmiş olanlar; başlık, özet, kapak resmi, bağlantı.
 * Google News, Microsoft Start, Flipboard, Feedly gibi platformlar bu adresi okuyup yeni yazıları kendileri gösterir.
 * Bir saat önbellekte tutulur.
 */
class BlogFeedController extends Controller
{
    public const LIMIT = 30;

    public function index()
    {
        $locale = app()->getLocale();
        $xml = Cache::remember("blog-feed:{$locale}", now()->addHour(), fn () => $this->build($locale));

        return response($xml, 200, [
            'Content-Type' => 'application/rss+xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    private function build(string $locale): string
    {
        $posts = Blog::published()->whereNotNull('slug')
            ->orderByDesc('published_at')->orderByDesc('id')
            ->limit(self::LIMIT * 3)->get()
            // yalnızca bu dile çevrilmiş yazılar (İngilizce yazı Türkçe akışa düşmesin)
            ->filter(fn (Blog $post) => filled($post->getTranslation('title', $locale, false)))
            ->take(self::LIMIT);

        $url = fn (string $path) => LaravelLocalization::getLocalizedURL($locale, $path, [], true);
        $e = fn (?string $text) => htmlspecialchars((string) $text, ENT_XML1 | ENT_QUOTES, 'UTF-8');
        $self = $url(route('blog.feed', [], false));

        $items = $posts->map(function (Blog $post) use ($locale, $url, $e) {
            $link = $url(route('blog.show', ['slug' => $post->localSlug($locale)], false));
            $summary = $post->getTranslation('excerpt', $locale, false) ?: $post->getTranslation('meta_description', $locale, false);
            $date = ($post->published_at ?? $post->created_at)?->toRfc2822String();
            $image = $post->coverUrl();

            return '<item>'
                . '<title>' . $e($post->getTranslation('title', $locale, false)) . '</title>'
                . '<link>' . $e($link) . '</link>'
                . '<guid isPermaLink="true">' . $e($link) . '</guid>'
                . ($date ? '<pubDate>' . $date . '</pubDate>' : '')
                . '<description>' . $e(strip_tags((string) $summary)) . '</description>'
                . ($image ? '<media:content url="' . $e($image) . '" medium="image" /><enclosure url="' . $e($image) . '" type="image/webp" length="0" />' : '')
                . '</item>';
        })->implode('');

        $latest = $posts->first()?->published_at ?? now();

        return '<?xml version="1.0" encoding="UTF-8"?>'
            . '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:media="http://search.yahoo.com/mrss/">'
            . '<channel>'
            . '<title>' . $e(__('TripSpoiler Blog')) . '</title>'
            . '<link>' . $e($url(route('blog.index', [], false))) . '</link>'
            . '<atom:link href="' . $e($self) . '" rel="self" type="application/rss+xml" />'
            . '<description>' . $e(__('Travel guides, tips and itineraries from TripSpoiler.')) . '</description>'
            . '<language>' . $e($locale) . '</language>'
            . '<lastBuildDate>' . $latest->toRfc2822String() . '</lastBuildDate>'
            . '<image><url>' . $e(asset('android-chrome-512x512.png')) . '</url><title>' . $e(__('TripSpoiler Blog')) . '</title><link>' . $e($url(route('blog.index', [], false))) . '</link></image>'
            . $items
            . '</channel></rss>';
    }
}
