<?php

namespace App\Http\Controllers;

use App\Models\Visit;
use App\Support\Tracker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Sayfadan çıkarken (ya da sekme arka plana geçince) tarayıcı bildirir (resources/js/track.js):
 * sayfada aktif kalınan süre, en fazla nereye kadar kaydırıldığı ve o sırada yapılan tıklamalar.
 * Bilgi, aynı sayfa açılışının görüntüleme kaydına (visits.view_key) eklenir; tıklamalar page_clicks'e (panel › sıcaklık haritası).
 */
class TrackLeaveController extends Controller
{
    private const MAX_CLICKS = 30;

    public function store(Request $request)
    {
        if (!Tracker::engagementReady() || Tracker::isBot($request) || !Tracker::sameOrigin($request)) {
            return response()->noContent();
        }
        $data = $request->validate([
            'pv' => ['required', 'string', 'regex:/^[A-Za-z0-9]{16,24}$/'],
            'd' => ['nullable', 'integer', 'min:0'],
            's' => ['nullable', 'integer', 'min:0', 'max:100'],
            'h' => ['nullable', 'integer', 'min:0', 'max:10000'],
            'c' => ['nullable', 'string', 'max:20000'],
        ]);

        // yalnızca son bir gündeki açılış (eski / uydurma kimlik bir şey değiştirmez)
        $visit = Visit::query()->where('view_key', $data['pv'])
            ->where('created_at', '>=', now(Tracker::TIMEZONE)->subDay()->format('Y-m-d H:i:s'))
            ->first(['id', 'path', 'device', 'duration', 'scroll']);
        if (!$visit) {
            return response()->noContent();
        }

        // süre en fazla 3 saat; sayfa birden çok kez bildirir (sekme değişince), hep en büyüğü kalır
        $updates = array_filter([
            'duration' => isset($data['d']) ? max((int) $visit->duration, min((int) $data['d'], 10800)) : null,
            'scroll' => isset($data['s']) ? max((int) $visit->scroll, (int) $data['s']) : null,
            'viewport_h' => !empty($data['h']) ? (int) $data['h'] : null,
        ], fn ($value) => $value !== null);
        if ($updates) {
            Visit::query()->whereKey($visit->id)->update($updates);
        }

        $clicks = json_decode((string) ($data['c'] ?? ''), true);
        if (is_array($clicks) && $clicks) {
            // bir sayfa açılışında en fazla 30 tıklama saklanır
            $room = self::MAX_CLICKS - DB::table('page_clicks')->where('visit_id', $visit->id)->count();
            $rows = [];
            foreach (array_slice($clicks, 0, max(0, $room)) as $click) {
                if (!is_array($click) || !is_numeric($click['x'] ?? null) || !is_numeric($click['y'] ?? null) || !is_numeric($click['w'] ?? null)) {
                    continue;
                }
                $label = trim(preg_replace('/\s+/u', ' ', (string) ($click['t'] ?? '')));
                $href = (string) ($click['h'] ?? '');
                $rows[] = [
                    'visit_id' => $visit->id,
                    'path' => $visit->path,
                    'path_hash' => sha1($visit->path),
                    'device' => $visit->device,
                    'x' => max(0, min(65535, (int) $click['x'])),
                    'y' => max(0, min(4294967295, (int) $click['y'])),
                    'vw' => max(1, min(65535, (int) $click['w'])),
                    'ph' => is_numeric($click['p'] ?? null) ? max(0, min(4294967295, (int) $click['p'])) : null,
                    'label' => $label !== '' ? Str::limit($label, 117) : null,
                    'href' => preg_match('#^(/|https?://)#i', $href) ? Str::limit($href, 497) : null,
                    'at' => is_numeric($click['a'] ?? null) ? max(0, min(10800, (int) $click['a'])) : null,
                    'rage' => !empty($click['r']),
                    'dead' => !empty($click['k']),
                    'created_at' => Tracker::now(),
                ];
            }
            if ($rows) {
                DB::table('page_clicks')->insert($rows);
            }
        }

        return response()->noContent();
    }
}
