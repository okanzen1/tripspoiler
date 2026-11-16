<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

/**
 * Sayfanın başka dildeki adresi (dil seçici, hreflang, canonical). Yazı / aktivite sayfasında her dilin kendi
 * adresi var (HasLocalizedSlugs); controller sayfanın kaydını current() ile bildirir.
 */
class LocalUrl
{
    private static ?array $current = null;

    public static function current(string $routeName, Model $model): void
    {
        self::$current = [$routeName, $model];
    }

    public static function to(string $locale): string
    {
        if (self::$current && request()->route()?->getName() === self::$current[0]) {
            [$name, $model] = self::$current;

            return (string) LaravelLocalization::getLocalizedURL($locale, route($name, $model->localSlug($locale), false), [], true);
        }

        return (string) LaravelLocalization::getLocalizedURL($locale, null, [], true);
    }
}
