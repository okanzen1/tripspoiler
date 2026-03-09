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

        $cities = City::where('active', true)->get();
        $blogs = Blog::where('status', true)->get();
        $activities = Activity::where('status', true)->get();

        foreach ($locales as $locale => $properties) {

            /*
            |--------------------------------------------------------------------------
            | Static Pages
            |--------------------------------------------------------------------------
            */

            $sitemap->add(
                Url::create(
                    LaravelLocalization::getLocalizedURL($locale, route('home', [], false))
                )
                ->setPriority(1.0)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
            );

            $sitemap->add(
                Url::create(
                    LaravelLocalization::getLocalizedURL($locale, route('activities.index', [], false))
                )
                ->setPriority(0.8)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
            );

            $sitemap->add(
                Url::create(
                    LaravelLocalization::getLocalizedURL($locale, route('cities.index', [], false))
                )
                ->setPriority(0.8)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
            );

            $sitemap->add(
                Url::create(
                    LaravelLocalization::getLocalizedURL($locale, route('blog.index', [], false))
                )
                ->setPriority(0.7)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
            );

            $sitemap->add(
                Url::create(
                    LaravelLocalization::getLocalizedURL($locale, route('about', [], false))
                )->setPriority(0.5)
            );

            $sitemap->add(
                Url::create(
                    LaravelLocalization::getLocalizedURL($locale, route('privacy', [], false))
                )->setPriority(0.5)
            );

            $sitemap->add(
                Url::create(
                    LaravelLocalization::getLocalizedURL($locale, route('contact', [], false))
                )->setPriority(0.5)
            );


            /*
            |--------------------------------------------------------------------------
            | Cities
            |--------------------------------------------------------------------------
            */

            foreach ($cities as $city) {

                $slug = $city->getTranslation('slug', $locale)
                    ?? $city->getTranslation('slug', 'en');

                if (!$slug) continue;

                $sitemap->add(
                    Url::create(
                        LaravelLocalization::getLocalizedURL(
                            $locale,
                            route('cities.show', ['slug' => $slug], false)
                        )
                    )
                    ->setPriority(0.7)
                    ->setLastModificationDate($city->updated_at)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Blogs
            |--------------------------------------------------------------------------
            */

            foreach ($blogs as $post) {

                $slug = $post->getTranslation('slug', $locale)
                    ?? $post->getTranslation('slug', 'en');

                if (!$slug) continue;

                $sitemap->add(
                    Url::create(
                        LaravelLocalization::getLocalizedURL(
                            $locale,
                            route('blog.show', ['slug' => $slug], false)
                        )
                    )
                    ->setPriority(0.6)
                    ->setLastModificationDate($post->updated_at)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Activities
            |--------------------------------------------------------------------------
            */

            foreach ($activities as $activity) {

                $slug = $activity->getTranslation('slug', $locale)
                    ?? $activity->getTranslation('slug', 'en');

                if (!$slug) continue;

                $sitemap->add(
                    Url::create(
                        LaravelLocalization::getLocalizedURL(
                            $locale,
                            route('activities.show', ['slug' => $slug], false)
                        )
                    )
                    ->setPriority(0.7)
                    ->setLastModificationDate($activity->updated_at)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                );
            }

        }

        return $sitemap->toResponse(request());
    }
}