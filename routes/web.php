<?php

use Illuminate\Support\Facades\Route;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

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
            ->middleware('throttle:30,1');


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
            ->name('newsletter.subscribe');


        /*
    |--------------------------------------------------------------------------
    | Static Pages
    |--------------------------------------------------------------------------
    */

        Route::view('/privacy', 'other.privacy')->name('privacy');

        Route::view('/about', 'other.about')->name('about');

        Route::view('/contact', 'other.contact')->name('contact');

        Route::post('/contact', [ContactController::class, 'submit'])
            ->name('contact.submit')
            ->middleware('throttle:5,1');

    /*
    |--------------------------------------------------------------------------
    | Reviews
    |--------------------------------------------------------------------------
    */
        Route::post('/reviews', [ReviewController::class, 'store'])
            ->middleware('throttle:5,1')
            ->name('reviews.store');

    }
    
);
