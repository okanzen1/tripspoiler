<?php

namespace App\View\Components;

use App\Models\Activity;
use App\Models\Blog;
use App\Models\City;
use Illuminate\View\Component;

/**
 * "TripSpoiler nedir": solda tanıtım, sağda sitedeki gerçek sayılarla dört kısayol kartı.
 */
class TripspoilerIntro extends Component
{
    public function render()
    {
        if (!\App\Support\SiteSections::enabled('intro')) {
            return '';
        }

        return view('components.tripspoiler-intro', [
            'counts' => [
                'cities' => City::visible()->count(),
                'experiences' => Activity::visible()->where('type', '!=', 'pass')->count(),
                'passes' => Activity::visible()->where('type', 'pass')->count(),
                'stories' => Blog::published()->count(),
            ],
        ]);
    }
}
