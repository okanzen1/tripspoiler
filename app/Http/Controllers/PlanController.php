<?php

namespace App\Http\Controllers;

use App\Mail\HelpRequestNotification;
use App\Mail\HelpRequestReceived;
use App\Models\BlogSubscriber;
use App\Models\City;
use App\Models\HelpRequest;
use App\Support\PlanTrip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Throwable;

/**
 * "Plan your trip": misafir adım adım ne istediğini anlatır (şehir, tarih, kimle, neye yardım lazım, konaklama,
 * tarz, not). Talep şifreli kaydedilir (App\Models\HelpRequest), misafire kendi dilinde önerili bir e-posta,
 * bize kısa bir bildirim gider; cevabı panelden (Yardım talepleri) yazarız.
 */
class PlanController extends Controller
{
    public function show()
    {
        abort_unless(PlanTrip::available(), 404);

        return view('other.plan', ['cities' => PlanTrip::cities()]);
    }

    public function store(Request $request)
    {
        abort_unless(PlanTrip::available(), 404);

        // Honeypot: botlar doldurur
        if ($request->filled('website')) {
            return response()->json(['ok' => true]);
        }

        $in = fn (array $values) => Rule::in($values);
        $data = $request->validate([
            'cities' => ['required', 'array', 'min:1', 'max:3'],
            'cities.*' => ['integer', Rule::exists('cities', 'id')],
            'other_city' => ['nullable', 'string', 'max:80'],
            'dates_known' => ['required', 'boolean'],
            'start_date' => ['nullable', 'required_if:dates_known,1', 'date', 'after_or_equal:today'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'month' => ['nullable', 'date_format:Y-m'],
            'days' => ['nullable', 'integer', 'min:1', 'max:30'],
            'first_time' => ['nullable', $in(['yes', 'no'])],
            'group' => ['required', $in(PlanTrip::GROUPS)],
            'adults' => ['nullable', 'integer', 'min:1', 'max:30'],
            'children' => ['nullable', 'integer', 'min:0', 'max:15'],
            'child_ages' => ['nullable', 'string', 'max:60'],
            'access' => ['nullable', 'array'], 'access.*' => [$in(PlanTrip::ACCESS)],
            'needs' => ['required', 'array', 'min:1'], 'needs.*' => [$in(PlanTrip::NEEDS)],
            'stay_vibe' => ['nullable', $in(PlanTrip::STAY_VIBE)],
            'stay_budget' => ['nullable', $in(PlanTrip::STAY_BUDGET)],
            'stay_extras' => ['nullable', 'array'], 'stay_extras.*' => [$in(PlanTrip::STAY_EXTRAS)],
            'food' => ['nullable', 'array'], 'food.*' => [$in(PlanTrip::FOOD)],
            'occasion' => ['nullable', $in(PlanTrip::OCCASION)],
            'pace' => ['nullable', $in(PlanTrip::PACE)],
            'interests' => ['nullable', 'array'], 'interests.*' => [$in(PlanTrip::INTERESTS)],
            'budget' => ['nullable', $in(PlanTrip::BUDGET)],
            'note' => ['nullable', 'string', 'max:2000'],
            'first_name' => ['required', 'string', 'max:60'],
            'last_name' => ['required', 'string', 'max:60'],
            'email' => ['required', 'email:rfc', 'max:180'],
            'consent' => ['accepted'],
            'newsletter' => ['nullable', 'boolean'],
        ]);

        $answers = collect($data)->except(['first_name', 'last_name', 'email', 'consent', 'newsletter'])
            ->filter(fn ($v) => $v !== null && $v !== [] && $v !== '')->all();
        $answers['city_names'] = City::whereIn('id', $data['cities'])->pluck('name')->all();
        if (filled($data['other_city'] ?? null)) {
            $answers['city_names'][] = trim($data['other_city']);
        }

        $email = mb_strtolower(trim($data['email']));
        $item = HelpRequest::create([
            'name' => trim($data['first_name']) . ' ' . trim($data['last_name']),
            'email' => $email,
            'answers' => $answers,
            'locale' => app()->getLocale(),
            'status' => 'new',
            'newsletter' => (bool) ($data['newsletter'] ?? false),
        ]);

        // İsteğe bağlı bülten (ayrı onay)
        if ($item->newsletter) {
            $subscriber = BlogSubscriber::where('email_hash', BlogSubscriber::hash($email))->first() ?? new BlogSubscriber(['email' => $email]);
            $subscriber->fill(['unsubscribed_at' => null, 'source_path' => '/plan-your-trip'])->save();
        }

        // E-postalar talebi düşürmesin: kayıt zaten panelde
        try {
            Mail::to($email)->locale(app()->getLocale())->send(new HelpRequestReceived($item, PlanTrip::suggestions($answers)));
            if ($to = config('mail.contact_to')) {
                Mail::to($to)->send(new HelpRequestNotification($item));
            }
        } catch (Throwable $e) {
            Log::warning('Yardım talebi e-postası gönderilemedi.', ['id' => $item->id, 'error' => $e->getMessage()]);
        }

        return $request->expectsJson() ? response()->json(['ok' => true]) : back()->with('plan_sent', true);
    }
}
