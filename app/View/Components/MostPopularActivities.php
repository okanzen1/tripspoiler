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
        public bool $popularOnly = true,
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
            . "_ex_" . ($this->excludeId ?? 'none') . ($this->popularOnly ? '_popular' : '_all');

        $query = function () {

            return Activity::query()
                ->with(['cover', 'city'])
                ->when(!empty($this->cityId), function ($q) {
                    $q->where('city_id', $this->cityId);
                })
                ->visible()
                ->when($this->popularOnly, fn ($q) => $q->where('is_popular', true))
                ->when($this->excludeId, fn ($q) => $q->whereKeyNot($this->excludeId))
                ->orderBy('sort_order')
                ->limit($this->limit)
                ->get();
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
