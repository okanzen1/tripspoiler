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
        public ?string $page = null,
        public ?int $cityId = null,
    ) {}

    public function render(): View|string
    {
        $locale = app()->getLocale();
        $bgColor = $this->bgColor ?? 'bg-white';

        $faqs = FaqModel::query()
            ->where('active', true)
            ->when($this->source === 'activity', fn($q) => $q->where('activity_id', $this->sourceId))
            // Sayfa bölümü: o sayfaya yazılan sorular + seçili şehre yazılan sorular (panelde "Nerede görünecek")
            // page_key + city_id birlikte: o sayfa, sadece o şehir seçiliyken; sadece city_id: şehre göre değişen bütün sayfalar
            ->when($this->source === 'page', fn($q) => $q->where(fn($w) => $w
                ->where(fn($p) => $p->where('page_key', $this->page)->where(fn($c) => $c->whereNull('city_id')->when($this->cityId, fn($c) => $c->orWhere('city_id', $this->cityId))))
                ->when($this->cityId, fn($w) => $w->orWhere(fn($c) => $c->whereNull('page_key')->where('city_id', $this->cityId)))))
            ->when($this->source === 'blog', fn($q) => $q->where('blog_id', $this->sourceId))
            ->when(!in_array($this->source, ['activity', 'blog', 'page']), fn($q) => $q->general())
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        if ($faqs->isEmpty()) {
            return '';
        }

        /*
        |--------------------------------------------------------------------------
        | FAQ Schema
        |--------------------------------------------------------------------------
        */

        $faqSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            '@id' => url()->current().'#faq',
            'url' => url()->current(),
            'mainEntity' => [],
        ];

        foreach ($faqs as $faq) {

            $question = $faq->getTranslation('question', $locale)
                ?? $faq->getTranslation('question', 'en');

            $answer = $faq->getTranslation('answer', $locale)
                ?? $faq->getTranslation('answer', 'en');

            $faqSchema['mainEntity'][] = [
                '@type' => 'Question',
                'name' => trim(strip_tags($question)),
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => trim(strip_tags($answer)),
                ],
            ];
        }

        return view('components.faq', compact('faqs', 'locale', 'bgColor', 'faqSchema'));
    }
}