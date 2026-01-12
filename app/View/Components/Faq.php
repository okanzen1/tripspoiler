<?php

namespace App\View\Components;

use App\Models\Faq as FaqModel;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Faq extends Component
{
    public function __construct(
        public ?string $source = null,
        public ?int $sourceId = null
    ) {}

    public function render(): View|string
    {
        $locale = app()->getLocale();

        $faqs = FaqModel::query()
            ->where('status', true)
            ->when($this->source, fn ($q) => $q->where('source', $this->source))
            ->when($this->sourceId, fn ($q) => $q->where('source_id', $this->sourceId))
            ->orderBy('sort_order')
            ->get();

        if ($faqs->isEmpty()) {
            return '';
        }

        return view('components.faq', compact('faqs', 'locale'));
    }
}
