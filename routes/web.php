<?php

use App\Http\Controllers\ContactController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {return view('home'); });
Route::get('/activities', function () {return view('activities.index'); });
Route::get('/museums', function () {return view('museums.index'); });
Route::get('/cities', function () {return view('cities.index'); });
Route::get('/blog', function () {return view('blog.index'); });
Route::view('/about', 'other.about');

Route::view('/contact', 'other.contact');

Route::post('/contact', [ContactController::class, 'submit'])
->name('contact.submit')
->middleware('throttle:5,1');

Route::view('/privacy', 'other.privacy');
