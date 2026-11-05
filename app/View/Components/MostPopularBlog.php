<?php

namespace App\View\Components;

use App\Models\Blog;
use App\Models\City;
use App\Models\SectionText;
use App\Models\Setting;
use Illuminate\View\Component;

/**
 * Blog bölümü (panel > Site ayarları > Blog bölümü): başlık, öne çıkan 3 yazı ve bülten kutusu.
 * Şehir sayfasında ($cityId) o şehrin en yeni yazıları; anasayfada paneldeki seçim, yoksa en yeniler.
 */
class MostPopularBlog extends Component
{
    public function __construct(public $cityId = null, public ?int $activityId = null) {}

    public function render()
    {
        if (!\App\Support\SiteSections::enabled('blog')) {
            return '';
        }

        $locale = app()->getLocale();
        $query = Blog::published()->with('city');

        if ($this->activityId) {
            // Aktivite detayı: o aktiviteye bağlanan yazılar, yoksa şehrin yazıları
            $blogs = (clone $query)->whereHas('activities', fn ($q) => $q->whereKey($this->activityId))->limit(3)->get();
            if ($blogs->isEmpty() && $this->cityId) {
                $blogs = (clone $query)->where('city_id', $this->cityId)->latest('published_at')->limit(3)->get();
            }
        } elseif ($this->cityId) {
            $blogs = (clone $query)->where('city_id', $this->cityId)->latest('published_at')->limit(3)->get();
        } else {
            $ids = array_values(array_filter(array_map('intval', explode(',', (string) Setting::many(['home_blog_ids'])['home_blog_ids']))));
            $blogs = $ids
                ? (clone $query)->whereIn('id', $ids)->get()->sortBy(fn ($blog) => array_search($blog->id, $ids))->values()
                : collect();
            // Seçilen yazı 3'ten azsa en yenilerle tamamlanır
            if ($blogs->count() < 3) {
                $blogs = $blogs->concat((clone $query)->whereNotIn('id', $blogs->pluck('id'))->latest('published_at')->limit(3 - $blogs->count())->get());
            }
        }

        if ($blogs->isEmpty()) {
            return '';
        }

        $texts = SectionText::texts('blog', $locale, [
            'eyebrow' => __('most-popular-blog.eyebrow'),
            'title' => __('most-popular-blog.title'),
            'title_highlight' => __('most-popular-blog.title_highlight'),
            'description' => __('most-popular-blog.desc'),
        ]);

        // Şehir sayfası: "Rehberler: Istanbul"
        if ($this->cityId && $city = City::find($this->cityId)) {
            $texts['title'] = __('most-popular-blog.city_title');
            $texts['title_highlight'] = $city->name;
        }

        return view('components.most-popular-blog', [
            'blogs' => $blogs,
            'texts' => $texts,
            // newsletter: title = başlık, description = açıklama, eyebrow = buton yazısı
            'newsletter' => SectionText::texts('newsletter', $locale, [
                'title' => __('newsletter-subscribe.title'),
                'description' => __('newsletter-subscribe.desc'),
                'eyebrow' => __('newsletter-subscribe.button'),
            ]),
        ]);
    }
}
