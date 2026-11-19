{{-- Bize: yeni yardım talebi. Ayrıntılar (tüm cevaplar) panelde: Yardım talepleri. --}}
@php $a = (array) $item->answers; @endphp
<div style="font-family: Arial, sans-serif; font-size: 14px; color: #0F172A; line-height: 1.6;">
    <p style="font-size: 16px; font-weight: bold;">Yeni yardım talebi</p>
    <p>
        <strong>Talep no:</strong> {{ $item->reference }}<br>
        <strong>Ad:</strong> {{ $item->name }}<br>
        <strong>E-posta:</strong> {{ $item->email }}<br>
        <strong>Şehir:</strong> {{ implode(', ', (array) ($a['city_names'] ?? [])) }}<br>
        <strong>Dil:</strong> {{ strtoupper($item->locale) }}
    </p>
    @if (filled($a['note'] ?? null))
        <p><strong>Not:</strong><br>{!! nl2br(e($a['note'])) !!}</p>
    @endif
    <p>Bütün cevaplar ve "Cevapla" düğmesi panelde: Yardım talepleri.</p>
</div>
