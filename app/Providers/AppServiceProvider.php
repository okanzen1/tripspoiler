<?php

namespace App\Providers;

use App\Models\Announcement;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Üstteki kayan duyuru şeridi: yayındaki duyurular, panelde verilen sırayla
        View::composer('partials.announcement-bar', function ($view) {
            $view->with('announcements', !\App\Support\SiteSections::enabled('announcements') || !\App\Support\PageSections::fixedEnabled('announcements') ? collect() : Announcement::active()
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get()
                ->filter(fn (Announcement $announcement) => filled($announcement->text))
                ->values());
        });
    }
}
