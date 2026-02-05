<?php

namespace App\Http\Controllers;

use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;
use App\Models\City;
use App\Models\Blog;

class SitemapController extends Controller
{
    public function index()
    {
        $sitemap = Sitemap::create();

        // Static pages
        $sitemap->add(Url::create(route('home'))->setPriority(1.0));
        $sitemap->add(Url::create(route('activities.index'))->setPriority(0.8));
        $sitemap->add(Url::create(route('cities.index'))->setPriority(0.8));
        $sitemap->add(Url::create(route('blog.index'))->setPriority(0.7));
        $sitemap->add(Url::create('/about')->setPriority(0.5));
        $sitemap->add(Url::create('/privacy')->setPriority(0.5));
        $sitemap->add(Url::create('/contact')->setPriority(0.5));

        // Cities
        City::where('active', true)->each(function (City $city) use ($sitemap) {
            $slug = $city->getTranslation('slug', app()->getLocale());
            if (!$slug) return;

            $sitemap->add(
                Url::create(route('cities.show', [
                    'slug' => $slug,
                    'id'   => $city->id,
                ]))
                ->setPriority(0.7)
            );
        });

        // Blog posts
        Blog::where('status', true)->each(function (Blog $post) use ($sitemap) {
            $slug = $post->getTranslation('slug', app()->getLocale());
            if (!$slug) return;

            $sitemap->add(
                Url::create(route('blog.show', [
                    'slug' => $slug,
                    'id'   => $post->id,
                ]))
                ->setPriority(0.6)
            );
        });

        return $sitemap->toResponse(request());
    }
}