<?php

namespace App\Http\Controllers;

use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use App\Models\City;
use App\Models\Blog;
use App\Models\Activity;

class SitemapController extends Controller
{
    public function index()
    {
        $sitemap = Sitemap::create();

        $locales = LaravelLocalization::getSupportedLocales();

        foreach ($locales as $locale => $properties) {

            app()->setLocale($locale);

            /*
            |--------------------------------------------------------------------------
            | Static pages
            |--------------------------------------------------------------------------
            */

            $sitemap->add(
                Url::create(route('home'))->setPriority(1.0)
            );

            $sitemap->add(
                Url::create(route('activities.index'))->setPriority(0.8)
            );

            $sitemap->add(
                Url::create(route('cities.index'))->setPriority(0.8)
            );

            $sitemap->add(
                Url::create(route('blog.index'))->setPriority(0.7)
            );

            $sitemap->add(
                Url::create("/{$locale}/about")->setPriority(0.5)
            );

            $sitemap->add(
                Url::create("/{$locale}/privacy")->setPriority(0.5)
            );

            $sitemap->add(
                Url::create("/{$locale}/contact")->setPriority(0.5)
            );

            /*
            |--------------------------------------------------------------------------
            | Cities
            |--------------------------------------------------------------------------
            */

            City::where('active', true)
                ->each(function (City $city) use ($sitemap, $locale) {

                    $slug = $city->getTranslation('slug', $locale)
                        ?? $city->getTranslation('slug', 'en');

                    if (!$slug) return;

                    $sitemap->add(
                        Url::create(route('cities.show', [
                            'slug' => $slug
                        ]))->setPriority(0.7)
                    );
                });

            /*
            |--------------------------------------------------------------------------
            | Blogs
            |--------------------------------------------------------------------------
            */

            Blog::where('status', true)
                ->each(function (Blog $post) use ($sitemap, $locale) {

                    $slug = $post->getTranslation('slug', $locale)
                        ?? $post->getTranslation('slug', 'en');

                    if (!$slug) return;

                    $sitemap->add(
                        Url::create(route('blog.show', [
                            'slug' => $slug
                        ]))->setPriority(0.6)
                    );
                });

            /*
            |--------------------------------------------------------------------------
            | Activities
            |--------------------------------------------------------------------------
            */

            Activity::where('status', true)
                ->each(function (Activity $activity) use ($sitemap, $locale) {

                    $slug = $activity->getTranslation('slug', $locale)
                        ?? $activity->getTranslation('slug', 'en');

                    if (!$slug) return;

                    $sitemap->add(
                        Url::create(route('activities.show', [
                            'slug' => $slug
                        ]))->setPriority(0.7)
                    );
                });
        }

        return $sitemap->toResponse(request());
    }
}