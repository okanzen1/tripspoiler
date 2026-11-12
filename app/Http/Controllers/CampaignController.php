<?php

namespace App\Http\Controllers;

use App\Models\Campaign;

/**
 * Kampanyalar sayfası (/campaigns). Yayında kampanya yoksa anasayfaya yönlendirir.
 */
class CampaignController extends Controller
{
    public function index()
    {
        $campaigns = Campaign::visible()->with('partner')->orderBy('sort_order')->orderByDesc('id')->get()
            ->filter(fn (Campaign $c) => filled($c->title))
            ->values();

        if ($campaigns->isEmpty()) {
            return redirect()->route('home');
        }

        return view('other.campaigns', ['campaigns' => $campaigns]);
    }
}
