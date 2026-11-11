<?php

namespace App\Providers;

use App\Models\Announcement;
use Illuminate\Support\Facades\Blade;
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
        // Gizlilik: ziyaretçi oturumlarına IP / tarayıcı yazılmaz (App\Support\PrivateSessionHandler)
        \Illuminate\Support\Facades\Session::extend('database', fn ($app) => new \App\Support\PrivateSessionHandler(
            $app['db']->connection($app['config']['session.connection']), $app['config']['session.table'], $app['config']['session.lifetime'], $app,
        ));

        // Yapısal veri (schema.org JSON-LD). JSON_HEX_TAG: veride "</script>" geçse bile (ör. yorum yapan kişinin adı)
        // script etiketi kapanmaz, XSS olmaz; JSON yine geçerlidir (< ve > \u003C / \u003E olarak yazılır).
        Blade::directive('jsonld', fn (string $expression) => '<script type="application/ld+json"><?php echo json_encode(' . $expression
            . ', JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_INVALID_UTF8_SUBSTITUTE); ?></script>');

        // Üstteki kayan duyuru şeridi: yayındaki duyurular, panelde verilen sırayla
        View::composer('partials.announcement-bar', function ($view) {
            $view->with('announcements', !\App\Support\SiteSections::enabled('announcements') || !\App\Support\PageSections::fixedEnabled('announcements') ? collect() : Announcement::active()
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get()
                ->filter(fn (Announcement $announcement) => filled($announcement->text))
                ->values());
        });

        // Duyuru şeridinin altındaki döviz kurları (şehir sayfasında o şehrin parası en başta)
        View::composer('partials.rates-bar', function ($view) {
            $city = $view->getData()['city'] ?? null;
            try {
                $rates = \App\Support\SiteSections::enabled('rates')
                    ? \App\Support\ExchangeRates::strip($city instanceof \App\Models\City ? $city : null)
                    : null;
            } catch (\Throwable $e) {
                // Ör. panelde migrate henüz çalışmadı (countries.currency yok): şerit gizlenir, sayfa bozulmaz
                report($e);
                $rates = null;
            }
            $view->with('rates', $rates);
        });
    }
}
