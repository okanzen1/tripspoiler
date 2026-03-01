<?php

namespace App\View\Components;

use App\Models\Faq as FaqModel;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Faq extends Component
{
    public function __construct(
        public ?string $source = null,
        public ?int $sourceId = null,
        public ?string $bgColor = null,
    ) {}

    public function render(): View|string
    {
        $locale = app()->getLocale();
        $bgColor = $this->bgColor ?? 'bg-[#FFF8F6]';

        $faqs = FaqModel::query()
            ->where('status', true)
            ->when($this->source, fn($q) => $q->where('source', $this->source))
            ->when($this->sourceId, fn($q) => $q->where('source_id', $this->sourceId))
            ->orderBy('sort_order')
            ->get();

        if ($faqs->isEmpty()) {
            return '';
        }

        // FAQ Schema oluştur
        $faqSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => [],
        ];

        foreach ($faqs as $faq) {
            $faqSchema['mainEntity'][] = [
                '@type' => 'Question',
                'name' => strip_tags($faq->getTranslation('question', $locale)),
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => strip_tags($faq->getTranslation('answer', $locale)),
                ],
            ];
        }

        return view('components.faq', compact('faqs', 'locale', 'bgColor', 'faqSchema'));
    }
}
