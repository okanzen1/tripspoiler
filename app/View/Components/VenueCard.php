<?php

namespace App\View\Components;

use Illuminate\View\Component;
use App\Models\Venue;
use Illuminate\Support\Collection;

class VenueCard extends Component
{
    public string $source;
    public int $sourceId;
    public Collection $venues;

    public function __construct(string $source, int $sourceId)
    {
        $this->source = $source;
        $this->sourceId = $sourceId;

        $this->venues = Venue::whereJsonContains('sources', $source)
            ->whereJsonContains('source_ids', $sourceId)
            ->with(['images'])
            ->where('status', true)
            ->orderBy('sort_order')
            ->get();
    }

    public function render()
    {
        if ($this->venues->isEmpty()) {
            return ''; // venue yoksa hiç basma
        }

        return view('components.venue-card', [
            'venues' => $this->venues,
        ]);
    }
}
