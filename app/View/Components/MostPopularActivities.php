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

    public function __construct(
        string $source = 'home',
        $sourceId = null,
        int $limit = 8
    ) {
        $this->source = $source;
        $this->sourceId = $sourceId;
        $this->limit = $limit;
    }

    public function activities()
    {
        $cacheKey = "most_popular_activities_{$this->source}_" . ($this->sourceId ?? 'null');
        
        return Cache::remember($cacheKey, now()->addMinutes(30), function () {
            $locale = app()->getLocale();
            return Activity::query()
                ->with([
                    'images' => function ($q) {
                        $q->orderBy('sort_order', 'asc')->limit(1);
                    }
                ])
                ->where('status', true)
                ->where('most_popular', true)
                ->orderBy('sort_order', 'asc')
                ->limit($this->limit)
                ->get();
        });
    }

    public function render()
    {
        $activities = $this->activities();

        if ($activities->isEmpty()) {
            return null;
        }

        return view('components.most-popular-activities', [
            'activities' => $activities
        ]);
    }
}
