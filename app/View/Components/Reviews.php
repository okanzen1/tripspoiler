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

        $limit = $source === 'home' ? 5 : 5;

        $query = Review::where('source', $source)
            ->when($sourceId, fn($q) => $q->where('source_id', $sourceId))
            ->where('approved', true);

        // Ortalama rating (tüm yorumlardan)
        $average = round($query->avg('rating') ?? 0, 1);

        // Slider için sadece 5 yıldızlı yorumlar
        $this->reviews = (clone $query)
            ->where('rating', 5)
            ->latest()
            ->take($limit)
            ->get();

        $this->general = [
            'averageRating' => $average,
            'stars' => round($average),
        ];
    }

    public function render()
    {
        return view('components.reviews');
    }
}