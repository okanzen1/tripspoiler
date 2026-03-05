<?php

namespace App\View\Components;

use Illuminate\View\Component;
use App\Models\Blog;
use App\Models\City;

class MostPopularBlog extends Component
{
    public $cityId;
    public $blogs;
    public $cityName = null;

    public function __construct($cityId = null)
    {
        $this->cityId = $cityId;
        $locale = app()->getLocale();

        /*
        |--------------------------------------------------------------------------
        | City Name
        |--------------------------------------------------------------------------
        */

        if (!empty($this->cityId)) {

            $city = City::select('id', 'name')
                ->find($this->cityId);

            if ($city) {

                $names = json_decode($city->getRawOriginal('name'), true);

                $this->cityName = $names[$locale]
                    ?? $names['en']
                    ?? null;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Blogs
        |--------------------------------------------------------------------------
        */

        $query = Blog::query()
            ->where('status', true);

        if (!empty($this->cityId)) {
            $query->where('city_id', $this->cityId);
        }

        $this->blogs = $query
            ->select('id', 'slug', 'title', 'excerpt')
            ->orderByDesc('click_count')
            ->limit(3)
            ->get()
            ->map(function ($row) use ($locale) {

                $titles = json_decode($row->getRawOriginal('title'), true) ?? [];
                $slug = json_decode($row->getRawOriginal('slug'), true) ?? [];
                $excerpts = json_decode($row->getRawOriginal('excerpt'), true) ?? [];

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
        if ($this->blogs->isEmpty()) {
            return '';
        }

        return view('components.most-popular-blog');
    }
}