<?php

namespace App\View\Components;

use Illuminate\View\Component;
use App\Models\Venue;
use Illuminate\Support\Collection;

class VenueCard extends Component
{
    public string $source;
    public int $sourceId;
    public ?int $id;
    public Collection $venues;

    public function __construct(string $source, int $sourceId, ?int $id = null)
    {
        $this->source = $source;
        $this->sourceId = $sourceId;
        $this->id = $id;

        $this->venues = Venue::query()
            ->when($id, function ($query) use ($id) {
                $query->where('id', $id);
            })
            ->whereJsonContains('sources', $source)
            ->whereJsonContains('source_ids', $sourceId)
            ->where('status', true)
            ->with('images')
            ->orderBy('sort_order')
            ->get();
    }

    public function render()
    {
        if ($this->venues->isEmpty()) {
            return '';
        }

        return view('components.venue-card', [
            'venues' => $this->venues,
        ]);
    }
}
