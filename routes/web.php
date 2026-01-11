<?php

use App\Http\Controllers\ContactController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NewsletterSubscribeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/activities', function () {
    return view('activities.index');
});
Route::get('/museums', function () {
    return view('museums.index');
});
Route::get('/cities', function () {
    return view('cities.index');
});
Route::get('/blog', function () {
    return view('blog.index');
});
Route::view('/about', 'other.about');
Route::view('/contact', 'other.contact');
Route::post('/contact', [ContactController::class, 'submit'])->name('contact.submit')->middleware('throttle:5,1');
Route::post(
    '/newsletter/subscribe',
    [NewsletterSubscribeController::class, 'store']
)->name('newsletter.subscribe');
Route::view('/privacy', 'other.privacy');
