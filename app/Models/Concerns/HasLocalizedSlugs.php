<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

/**
 * Dillere göre adres: "slugs" sütunu {"tr": "...", "de": "..."} (panelde başlık çevirisinden üretilir).
 * O dilde adres yoksa (ja, zh, ko, ar ya da henüz çevrilmemiş) İngilizce "slug" kullanılır.
 */
trait HasLocalizedSlugs
{
    public function initializeHasLocalizedSlugs(): void
    {
        $this->mergeCasts(['slugs' => 'array']);
    }

    public function localSlug(?string $locale = null): string
    {
        $slugs = $this->slugs ?? [];
        $slug = $slugs[$locale ?? app()->getLocale()] ?? null;

        return is_string($slug) && $slug !== '' ? $slug : (string) $this->slug;
    }

    /**
     * Adresteki slug ile bul: önce bu dilin adresi, sonra İngilizce adres, en son başka bir dilin adresi
     * (dil değiştirilmiş eski link). Bulunan kaydın doğru adresi farklıysa controller oraya yönlendirir.
     *
     * @param  Builder<static>  $query  ör. Blog::published()->with(...)
     */
    public static function firstBySlugOrFail(Builder $query, string $slug): static
    {
        $locales = array_filter(array_keys(\Mcamara\LaravelLocalization\Facades\LaravelLocalization::getSupportedLocales()), fn ($c) => preg_match('/^[a-z]{2}$/', $c));

        $found = $query->where(function (Builder $q) use ($slug, $locales) {
            $q->where('slug', $slug);
            foreach ($locales as $code) {
                $q->orWhere("slugs->{$code}", $slug);
            }
        })->limit(20)->get()
            ->sortBy(fn ($model) => $model->localSlug() === $slug ? 0 : ($model->slug === $slug ? 1 : 2))
            ->first();

        abort_if($found === null, 404);

        return $found;
    }
}
