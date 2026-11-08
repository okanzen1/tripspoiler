<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect;
use Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRedirectFilter;
use Mcamara\LaravelLocalization\Middleware\LaravelLocalizationViewPath;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(\App\Http\Middleware\CacheImages::class);
        // Analitik: her gerçek sayfa görüntülemesi (panel > Analitik)
        $middleware->web(append: [\App\Http\Middleware\TrackVisit::class]);
        // Tıklama bildirimi tarayıcıdan arka planda gelir (sendBeacon), CSRF anahtarı taşımaz
        $middleware->validateCsrfTokens(except: ['t/click']);
        $middleware->alias([
            'localeSessionRedirect' => LocaleSessionRedirect::class,
            'localizationRedirect' => LaravelLocalizationRedirectFilter::class,
            'localeViewPath' => LaravelLocalizationViewPath::class,
        ]);
    })

    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
