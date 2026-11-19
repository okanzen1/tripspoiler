{{--
    Misafire: "Talebini aldık" + seçimlerine uyan öneriler (şehir başına popüler aktiviteler, yerler, rehberler).
    E-posta istemcileri için tablo düzeni ve satır içi stiller. Dil: talebin gönderildiği dil (Mail::locale).
--}}
@php
    $t = fn (string $key, array $r = []) => __('plan-page.' . $key, $r);
    $a = (array) $item->answers;
    $first = explode(' ', trim($item->name))[0];
    // tarihler misafirin dilinde ("12 Ara 2026", "Aralık 2026")
    $day = fn ($d) => \Carbon\Carbon::parse($d)->locale(app()->getLocale())->isoFormat('D MMM YYYY');
    $dates = !empty($a['start_date'])
        ? $day($a['start_date']) . (!empty($a['end_date']) ? ' – ' . $day($a['end_date']) : '')
        : (!empty($a['month']) ? \Carbon\Carbon::createFromFormat('Y-m-d', $a['month'] . '-01')->locale(app()->getLocale())->isoFormat('MMMM YYYY') : null);
    $rows = array_filter([
        $t('mail_cities') => implode(', ', (array) ($a['city_names'] ?? [])),
        $t('mail_dates') => $dates,
        $t('days') => !empty($a['days']) && empty($a['start_date']) ? $t('mail_days', ['n' => $a['days']]) : null,
        $t('mail_group') => !empty($a['group']) ? $t('group_' . $a['group']) : null,
        $t('mail_needs') => implode(', ', array_map(fn ($n) => $t('needs_' . $n), (array) ($a['needs'] ?? []))),
    ]);
    $red = '#C62E2E';
@endphp
<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>{{ $t('mail_subject') }}</title></head>
<body style="margin:0;padding:0;background:#F1F5F9;font-family:Arial,Helvetica,sans-serif;color:#0F172A;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#F1F5F9;padding:24px 12px;">
<tr><td align="center">
<table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#FFFFFF;border-radius:24px;overflow:hidden;">
    {{-- üst --}}
    <tr><td style="background:#0F1420;padding:32px 32px 28px;">
        <p style="margin:0;font-size:20px;font-weight:800;color:#FFFFFF;letter-spacing:-0.3px;">Trip<span style="color:#FF6B6E;">Spoiler</span></p>
        <p style="margin:18px 0 0;font-size:26px;line-height:1.2;font-weight:800;color:#FFFFFF;">{{ $t('mail_hi', ['name' => $first]) }}</p>
        <p style="margin:10px 0 0;font-size:15px;line-height:1.6;color:#CBD5E1;">{{ $t('mail_intro') }}</p>
    </td></tr>

    {{-- talep özeti --}}
    <tr><td style="padding:28px 32px 8px;">
        <p style="margin:0 0 12px;font-size:12px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:{{ $red }};">{{ $t('mail_summary') }}</p>
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#F8FAFC;border-radius:16px;">
            @foreach ($rows as $label => $value)
                <tr>
                    <td style="padding:12px 16px;font-size:13px;color:#64748B;width:38%;border-bottom:1px solid #EEF2F7;">{{ $label }}</td>
                    <td style="padding:12px 16px;font-size:14px;font-weight:700;color:#0F172A;border-bottom:1px solid #EEF2F7;">{{ $value }}</td>
                </tr>
            @endforeach
        </table>
    </td></tr>

    @if (collect($suggestions)->contains(fn ($s) => $s['activities']->isNotEmpty() || $s['places']->isNotEmpty() || $s['blogs']->isNotEmpty()))
        <tr><td style="padding:24px 32px 0;">
            <p style="margin:0;font-size:18px;font-weight:800;color:#0F172A;">{{ $t('mail_while') }}</p>
        </td></tr>

        @foreach ($suggestions as $s)
            {{-- popüler aktiviteler --}}
            @if ($s['activities']->isNotEmpty())
                <tr><td style="padding:20px 32px 0;">
                    <p style="margin:0 0 12px;font-size:12px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:{{ $red }};">{{ $t('mail_activities', ['city' => $s['city']->name]) }}</p>
                    @foreach ($s['activities'] as $act)
                        @php $url = route('activities.show', $act->localSlug()); $img = $act->cover?->url('sm'); @endphp
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:10px;border:1px solid #E2E8F0;border-radius:16px;">
                            <tr>
                                @if ($img)
                                    <td width="96" style="padding:10px;"><a href="{{ $url }}"><img src="{{ $img }}" width="86" height="64" alt="" style="display:block;width:86px;height:64px;object-fit:cover;border-radius:10px;border:0;"></a></td>
                                @endif
                                <td style="padding:10px 14px;">
                                    <a href="{{ $url }}" style="font-size:14px;font-weight:700;line-height:1.35;color:#0F172A;text-decoration:none;">{{ $act->name }}</a><br>
                                    <a href="{{ $url }}" style="font-size:13px;font-weight:700;color:{{ $red }};text-decoration:none;">{{ $t('mail_see') }} &rarr;</a>
                                </td>
                            </tr>
                        </table>
                    @endforeach
                </td></tr>
            @endif

            {{-- yerler + rehberler --}}
            @if ($s['places']->isNotEmpty() || $s['blogs']->isNotEmpty())
                <tr><td style="padding:12px 32px 0;">
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#F8FAFC;border-radius:16px;">
                        @if ($s['places']->isNotEmpty())
                            <tr><td style="padding:16px 18px 4px;font-size:12px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:#64748B;">{{ $t('mail_places') }}</td></tr>
                            @foreach ($s['places'] as $place)
                                <tr><td style="padding:6px 18px;"><a href="{{ route('attractions.show', $place->localSlug()) }}" style="font-size:14px;font-weight:700;color:#0F172A;text-decoration:none;">&#9679;&nbsp; {{ $place->name }}</a></td></tr>
                            @endforeach
                        @endif
                        @if ($s['blogs']->isNotEmpty())
                            <tr><td style="padding:16px 18px 4px;font-size:12px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:#64748B;">{{ $t('mail_reads') }}</td></tr>
                            @foreach ($s['blogs'] as $blog)
                                <tr><td style="padding:6px 18px;"><a href="{{ route('blog.show', $blog->localSlug()) }}" style="font-size:14px;font-weight:700;color:#0F172A;text-decoration:none;">&#9679;&nbsp; {{ $blog->title }}</a></td></tr>
                            @endforeach
                        @endif
                        <tr><td style="padding:12px 18px 18px;"><a href="{{ route('cities.show', ['slug' => $s['city']->slug]) }}" style="font-size:14px;font-weight:700;color:{{ $red }};text-decoration:none;">{{ $t('mail_city_guide', ['city' => $s['city']->name]) }} &rarr;</a></td></tr>
                    </table>
                </td></tr>
            @endif
        @endforeach
    @endif

    {{-- kapanış --}}
    <tr><td style="padding:28px 32px 32px;">
        <p style="margin:0;font-size:15px;line-height:1.6;color:#334155;">{{ $t('mail_outro') }}</p>
        <p style="margin:14px 0 0;font-size:15px;font-weight:700;color:#0F172A;">{{ $t('mail_team') }}</p>
    </td></tr>
    <tr><td style="background:#F8FAFC;padding:18px 32px;border-top:1px solid #EEF2F7;">
        <p style="margin:0;font-size:12px;line-height:1.6;color:#94A3B8;">{{ $t('mail_privacy') }}</p>
    </td></tr>
</table>
</td></tr>
</table>
</body>
</html>
