<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

/**
 * "En popüler" sıralaması (panel: Aktiviteler):
 *  1. panelde yıldızla elle öne çıkarılanlar (is_popular),
 *  2. sonra Wikipedia ilgisi (son 30 gün okunma, panel her gece günceller),
 *  3. sonra paneldeki sıra.
 * Wikipedia sütunu henüz yoksa (panel migrate edilmeden önce) 3. adımla devam eder.
 */
class Popularity
{
    public static function rank(Builder $query): Builder
    {
        $query->orderByDesc('is_popular');
        if (self::hasWikiViews()) {
            $query->orderByRaw('COALESCE(wiki_views, 0) DESC');
        }

        return $query->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Şehirleri karıştır: sıralı listeden her şehrin en iyisi, sonra ikincisi… (en güçlü şehir başta).
     * $perCity: bir şehirden en fazla kaç tane.
     */
    public static function mix(Collection $ranked, int $limit, int $perCity = PHP_INT_MAX): Collection
    {
        $groups = $ranked->groupBy('city_id')->map->values()->values();
        $mixed = collect();

        for ($round = 0; $round < $perCity && $mixed->count() < $limit; $round++) {
            $added = false;
            foreach ($groups as $group) {
                if (isset($group[$round]) && $mixed->count() < $limit) {
                    $mixed->push($group[$round]);
                    $added = true;
                }
            }
            if (!$added) {
                break;
            }
        }

        return $mixed;
    }

    private static function hasWikiViews(): bool
    {
        static $has;
        if ($has !== null) {
            return $has;
        }
        if (Cache::get('schema:activities.wiki_views')) {
            return $has = true;
        }
        $has = Schema::hasColumn('activities', 'wiki_views');
        // var olduğu kesinleşince hep hatırlanır; yoksa 10 dakikada bir yeniden bakılır
        $has ? Cache::forever('schema:activities.wiki_views', true) : Cache::put('schema:activities.wiki_views', false, now()->addMinutes(10));

        return $has;
    }
}
