<?php

namespace App\Support;

use App\Models\Blog;
use Illuminate\Support\Collection;

/**
 * Aktivite ve yer sayfalarının altındaki "… gezi rehberleri": önce bu aktivite(ler)e bağlanmış yazılar, sonra aynı
 * şehrin yazıları, en yeniden. Sayfalar birbirine bağlanır (Google için iç bağlantı, ziyaretçi için okuma).
 */
class RelatedPosts
{
    /**
     * @param  list<int>  $activityIds
     * @return Collection<int, Blog>
     */
    public static function for(?int $cityId, array $activityIds = [], int $limit = 3): Collection
    {
        if (!$cityId && !$activityIds) {
            return collect();
        }

        return Blog::published()->whereNotNull('slug')
            ->where(fn ($q) => $q
                ->when($activityIds, fn ($q) => $q->orWhereHas('activities', fn ($a) => $a->whereIn('activities.id', $activityIds)))
                ->when($cityId, fn ($q) => $q->orWhere('city_id', $cityId)->orWhereHas('cities', fn ($c) => $c->whereKey($cityId))))
            ->withCount(['activities as linked' => fn ($a) => $a->whereIn('activities.id', $activityIds ?: [0])])
            ->orderByDesc('linked')->latest('published_at')->latest('id')
            ->limit($limit)->get();
    }
}
