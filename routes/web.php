<?php

use Illuminate\Support\Facades\Route;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use Illuminate\Http\Request;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\NewsletterSubscribeController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\ReviewController;


/*
|--------------------------------------------------------------------------
| Global Routes
|--------------------------------------------------------------------------
*/

Route::get('/sitemap.xml', [SitemapController::class, 'index']);
// Dil ve türe göre alt haritalar (sitemap.xml bunları listeler)
Route::get('/sitemaps/{locale}-{type}.xml', [SitemapController::class, 'show'])->where(['locale' => '[a-z]{2}', 'type' => '[a-z]+']);
// Yapay zekâ asistanları için site özeti (llmstxt.org)
Route::get('/llms.txt', [\App\Http\Controllers\LlmsController::class, 'index']);
// Analitik (resources/js/track.js): sayfa görüntüleme ve partner bağlantısına tıklama
Route::post('/t/view', [\App\Http\Controllers\TrackViewController::class, 'store'])->middleware('throttle:120,1')->name('track.view');
Route::post('/t/click', [\App\Http\Controllers\TrackClickController::class, 'store'])->middleware('throttle:60,1')->name('track.click');
// döviz kuru şeridindeki dünya hava durumu (sayfa açıldıktan sonra arka planda yüklenir)
Route::get('/w/ticker', [\App\Http\Controllers\WeatherTickerController::class, 'index'])->middleware('throttle:60,1')->name('weather.ticker');


/*
|--------------------------------------------------------------------------
| Localized Routes
|--------------------------------------------------------------------------
*/

Route::group(
    [
        'prefix' => LaravelLocalization::setLocale(),
        'middleware' => [
            'localeSessionRedirect',
            'localizationRedirect',
            'localeViewPath'
        ]
    ],
    function () {

        /*
    |--------------------------------------------------------------------------
    | Home
    |--------------------------------------------------------------------------
    */

        Route::get('/', [HomeController::class, 'index'])->name('home');


        /*
    |--------------------------------------------------------------------------
    | Activities
    |--------------------------------------------------------------------------
    */

        Route::prefix('activities')->name('activities.')->group(function () {

            Route::get('/', [ActivityController::class, 'index'])->name('index');

            Route::get('{slug}', [ActivityController::class, 'show'])
                ->name('show')
                ->where('slug', '.*');
        });


        /*
    |--------------------------------------------------------------------------
    | Cities
    |--------------------------------------------------------------------------
    */

        // Yer sayfaları (panel: Yerler)
        Route::get('/attractions/{slug}', [\App\Http\Controllers\AttractionController::class, 'show'])
            ->name('attractions.show')->where('slug', '[a-z0-9-]+');

        Route::prefix('cities')->name('cities.')->group(function () {

            Route::get('/', [CityController::class, 'index'])->name('index');

            Route::get('{slug}', [CityController::class, 'show'])
                ->name('show')
                ->where('slug', '.*');
        });


        /*
    |--------------------------------------------------------------------------
    | Blog
    |--------------------------------------------------------------------------
    */

        Route::prefix('blog')->name('blog.')->group(function () {

            Route::get('/', [BlogController::class, 'index'])->name('index');

            // RSS akışı (Google News, Microsoft Start, Flipboard, Feedly…): yazı adresinden önce tanımlı olmalı
            Route::get('feed', [\App\Http\Controllers\BlogFeedController::class, 'index'])->name('feed');

            // Travel News: güncel seyahat haberleri (şehir listelerinden ayrı)
            Route::get('travel-news', [BlogController::class, 'news'])->name('news');

            Route::get('{slug}', [BlogController::class, 'show'])
                ->name('show')
                ->where('slug', '.*');
        });


        /*
    |--------------------------------------------------------------------------
    | Search
    |--------------------------------------------------------------------------
    */

        Route::get('/search', [SearchController::class, 'search'])
            ->name('search')
            ->middleware('throttle:90,1');


        /*
    |--------------------------------------------------------------------------
    | Media
    |--------------------------------------------------------------------------
    */

        Route::get('/media/{image}', [ImageController::class, 'show'])
            ->name('images.view');


        /*
    |--------------------------------------------------------------------------
    | Newsletter
    |--------------------------------------------------------------------------
    */

        Route::post('/newsletter/subscribe', [NewsletterSubscribeController::class, 'store'])
            ->name('newsletter.subscribe')
            ->middleware('throttle:5,1');


        /*
    |--------------------------------------------------------------------------
    | Static Pages
    |--------------------------------------------------------------------------
    */

        Route::view('/privacy', 'other.privacy')->name('privacy');

        Route::view('/exchange-rates', 'other.rates')->name('rates');

        Route::get('/campaigns', [\App\Http\Controllers\CampaignController::class, 'index'])->name('campaigns');

        Route::get('/weather', [\App\Http\Controllers\WeatherController::class, 'index'])->name('weather');

        Route::get('/time', [\App\Http\Controllers\TimeController::class, 'index'])->name('time');

        Route::view('/about', 'other.about')->name('about');

        // Gezi planı: misafir ne istediğini anlatır, yardım talebi bırakır (App\Http\Controllers\PlanController)
        Route::get('/plan-your-trip', [\App\Http\Controllers\PlanController::class, 'show'])->name('plan');
        Route::post('/plan-your-trip', [\App\Http\Controllers\PlanController::class, 'store'])->name('plan.store')->middleware('throttle:5,1');
        Route::post('/plan-your-trip/status', [\App\Http\Controllers\PlanController::class, 'status'])->name('plan.status')->middleware('throttle:10,1');

        Route::view('/contact', 'other.contact')->name('contact');

        Route::post('/contact', [ContactController::class, 'submit'])
            ->name('contact.submit')
            ->middleware('throttle:5,1');

        /*
    |--------------------------------------------------------------------------
    | Reviews
    |--------------------------------------------------------------------------
    */
        Route::post('/reviews/go', [ReviewController::class, 'go'])->middleware('throttle:30,1')->name('reviews.go');
        Route::get('/reviews', [ReviewController::class, 'index'])->name('reviews.index');
        Route::post('/reviews', [ReviewController::class, 'store'])->middleware('throttle:5,1')->name('reviews.store');


        Route::post('/set-city', function (Request $request) {
            $data = $request->validate(['city_id' => ['required', 'integer', 'min:1']]);
            session(['selected_city_id' => (int) $data['city_id']]);
            return response()->json(['ok' => true]);
        })->middleware('throttle:30,1');
    }

);
