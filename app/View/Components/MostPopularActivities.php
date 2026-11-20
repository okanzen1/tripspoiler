<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\Support\Facades\Cache;
use App\Models\Activity;

class MostPopularActivities extends Component
{
    public string $source;
    public $sourceId;
    public int $limit;
    public ?int $cityId;

    public function __construct(
        string $source = 'home',
        $sourceId = null,
        int $limit = 8,
        ?int $cityId = null,
        public ?int $excludeId = null,
        public bool $popularOnly = true, // eski çağrılar için; sıralama artık App\Support\Popularity
    ) {
        $this->source = $source;
        $this->sourceId = $sourceId;
        $this->limit = $limit;
        $this->cityId = $cityId;
    }

    public function activities()
    {
        $locale = app()->getLocale();

        $cacheKey = "most_popular_activities_{$locale}_{$this->source}_"
            . ($this->sourceId ?? 'null')
            . "_city_" . ($this->cityId ?? 'all')
            . "_ex_" . ($this->excludeId ?? 'none') . '_ranked';

        // Şehir verilmişse o şehrin en popülerleri. Anasayfada (şehir yok) HER şehrin en popüler ürünü
        // (şehir sırası rastgele, sınır yok); şehir azsa diğer ürünlerle $limit'e tamamlanır.
        $query = function () {
            $ranked = \App\Support\Popularity::rank(Activity::query()
                ->with(['cover', 'city'])
                ->when(!empty($this->cityId), fn ($q) => $q->where('city_id', $this->cityId))
                ->visible()
                ->when($this->excludeId, fn ($q) => $q->whereKeyNot($this->excludeId)));

            if (!empty($this->cityId)) {
                return $ranked->limit($this->limit)->get();
            }

            $all = $ranked->get();
            $list = \App\Support\Popularity::mix($all, PHP_INT_MAX, perCity: 1, shuffleCities: true);
            if ($list->count() < $this->limit) {
                $list = $list->concat(\App\Support\Popularity::mix(
                    $all->whereNotIn('id', $list->pluck('id')->all())->values(),
                    $this->limit - $list->count(),
                ));
            }

            return $list->values();
        };

        if (config('app.global_cache_enabled')) {
            return Cache::remember($cacheKey, now()->addMinutes(30), $query);
        }

        return $query();
    }


    public function render()
    {
        $activities = $this->activities();

        if ($activities->isEmpty()) {
            return '';
        }

        return view('components.most-popular-activities', [
            'activities' => $activities
        ]);
    }
}
