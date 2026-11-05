<?php

namespace App\View\Components;

use App\Models\SectionText;
use App\Models\Step;
use Illuminate\View\Component;

/**
 * "Nasıl çalışır" bölümü: başlık yazıları ve adımlar paneldan (Site ayarları > Nasıl çalışır).
 */
class StepsSection extends Component
{
    public function render()
    {
        if (!\App\Support\SiteSections::enabled('steps')) {
            return '';
        }

        $steps = Step::active()->orderBy('sort_order')->orderBy('id')->get()
            ->filter(fn (Step $step) => filled($step->title))->values();

        if ($steps->isEmpty()) {
            return '';
        }

        return view('components.steps-section', [
            'steps' => $steps,
            'texts' => SectionText::texts('steps', app()->getLocale(), [
                'eyebrow' => __('steps-section.eyebrow'),
                'title' => __('steps-section.title'),
                'title_highlight' => __('steps-section.title_highlight'),
                'description' => __('steps-section.desc'),
            ]),
        ]);
    }
}
