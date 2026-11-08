<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// IP → ülke/şehir veritabanı (DB-IP Lite) her ayın 3'ünde yenilenir (canlıda cron: * * * * * php artisan schedule:run)
Illuminate\Support\Facades\Schedule::command('geoip:update')->monthlyOn(3, '05:00')->withoutOverlapping();
