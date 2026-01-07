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
    ) {
    }

    public function render(): View|string
    {
        $locale = app()->getLocale();

        $rows = FaqModel::query()
            ->where('status', true)
            ->when($this->source, fn ($q) => $q->where('source', $this->source))
            ->when($this->sourceId, fn ($q) => $q->where('source_id', $this->sourceId))
            ->orderBy('sort_order')
            ->get(['question', 'answer']);

        if ($rows->isEmpty()) {
            return '';
        }

        $faqs = array_map(
            fn ($row) => [
                'question' => $row['question'][$locale]
                    ?? $row['question']['en']
                    ?? '',

                'answer' => $row['answer'][$locale]
                    ?? $row['answer']['en']
                    ?? '',
            ],
            $rows->all()
        );

        return view('components.faq', compact('faqs'));
    }
}
