<?php

use App\Http\Controllers\ContactController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NewsletterSubscribeController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CityController;


// home page
    Route::get('/', [HomeController::class, 'index'])->name('home');
// home page

// activities page
    Route::get('/activities', function () { return view('activities.index');});
// activities page

// museums page
    Route::get('/museums', function () {return view('museums.index');});
// museums page

// City Routes
    Route::get('/cities', [CityController::class, 'index'])->name('cities.index');
    Route::get('/cities/{slug}-{id}', [CityController::class, 'show'])->name('cities.show');
// City Routes

// Blog Routes
    Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
    Route::get('/blog/{slug}-{id}', [BlogController::class, 'show'])->name('blog.show')->where(['slug' => '[-a-zA-Z0-9_]+', 'id' => '[0-9]+']);
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
