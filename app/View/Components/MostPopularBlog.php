<?php

namespace App\View\Components;

use Illuminate\View\Component;
use App\Models\Blog;

class MostPopularBlog extends Component
{
    public $source;
    public $sourceId;
    public $blogs;

    public function __construct($source = null, $sourceId = null)
    {
        $this->source = $source;
        $this->sourceId = $sourceId;

        $locale = app()->getLocale();

        $this->blogs = Blog::where('status', true)
            ->orderByDesc('click_count')
            ->limit(3)
            ->get()
            ->map(function ($row) use ($locale) {

                $titles = json_decode($row->getRawOriginal('title'), true);
                $slug = json_decode($row->getRawOriginal('slug'), true);
                $excerpts = json_decode($row->getRawOriginal('excerpt'), true);

                return [
                    'id' => $row->id,
                    'slug' => $slug[$locale] ?? $slug['en'] ?? '',
                    'title' => $titles[$locale] ?? $titles['en'] ?? '',
                    'excerpt' => $excerpts[$locale] ?? $excerpts['en'] ?? '',
                ];
            });
    }

    public function render()
    {
        // boşsa component hiç render edilmesin
        if ($this->blogs->isEmpty()) {
            return '';
        }

        return view('components.most-popular-blog');
    }
}
