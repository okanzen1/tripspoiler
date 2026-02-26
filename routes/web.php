<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NewsletterSubscribeController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CityController;
use App\Http\Controllers\SitemapController;

Route::get('/sitemap.xml', [SitemapController::class, 'index']);

// home page
Route::get('/', [HomeController::class, 'index'])->name('home');
// home page

// activities page
Route::get('/activities', [ActivityController::class, 'index'])->name('activities.index');
Route::get('/activities/{slug}', [ActivityController::class, 'show'])->name('activities.show');
// activities page

// City Routes
Route::get('/cities', [CityController::class, 'index'])
    ->name('cities.index');

Route::get('/cities/{slug}', [CityController::class, 'show'])
    ->name('cities.show')
    ->where('slug', '[-a-zA-Z0-9_]+');
// City Routes

// Blog Routes
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show')->where(['slug' => '[-a-zA-Z0-9_]+']);
// Blog Routes

// Search Route
Route::get('/search', [SearchController::class, 'index'])->name('search.index');
// Search Route

// Media Routes
Route::get('/media/{image}', [ImageController::class, 'show'])->name('images.view');
// Media Routes

// Newsletter Subscription Route
Route::post('/newsletter/subscribe', [NewsletterSubscribeController::class, 'store'])->name('newsletter.subscribe');
// Newsletter Subscription Route

// privacy Pages
Route::view('/privacy', 'other.privacy');
// privacy Pages

// about Pages
Route::view('/about', 'other.about');
// about Pages

// Contact Routes
Route::view('/contact', 'other.contact');
Route::post('/contact', [ContactController::class, 'submit'])->name('contact.submit')->middleware('throttle:5,1');
// Contact Routes
