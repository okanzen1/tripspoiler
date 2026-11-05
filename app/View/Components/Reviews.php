<?php

namespace App\View\Components;

use App\Models\Review;
use Illuminate\View\Component;

class Reviews extends Component
{
    public $source;
    public $sourceId;
    public $reviews;
    public $general;

    public function __construct($source, $sourceId)
    {
        $this->source = $source;
        $this->sourceId = $sourceId;

        $query = Review::approved()
            ->when(
                $source === 'activity' && $sourceId,
                fn($q) => $q->where('activity_id', $sourceId),
                fn($q) => $q->whereNull('activity_id')
            );

        // Ortalama rating (tüm yorumlardan)
        $average = round($query->avg('rating') ?? 0, 1);

        // Kayan kartlar: 4-5 yıldızlı en yeni yorumlar
        $this->reviews = (clone $query)
            ->where('rating', '>=', 4)
            ->latest()
            ->take(9)
            ->get();

        $this->general = [
            'averageRating' => $average,
            'stars' => round($average),
            'count' => (clone $query)->count(),
        ];
    }

    public function render()
    {
        if (!\App\Support\SiteSections::enabled('reviews')) {
            return '';
        }

        return view('components.reviews');
    }
}