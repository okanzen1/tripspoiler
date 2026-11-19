{{--
    Üstteki kayan duyuru şeridi. Duyurular panelden (Duyurular) gelir; yayında olan yoksa hiç görünmez.
    Kısa metinlerde ekran boş kalmasın diye liste tekrarlanır; iki kopya yan yana kayarak kesintisiz döner.
    Kapatılırsa bu ziyaret boyunca gizli kalır (sessionStorage); siteye yeniden gelince tekrar görünür.
--}}
@if ($announcements->isNotEmpty())
    @php
        // Bir kopya en geniş ekranı (~2400 px) dolduracak kadar tekrarlanır; kısa duyurularda sağda boşluk kalmasın.
        // Tahmini genişlik: harf başına ~8 px + her öğede nokta ve boşluk ~70 px
        $oneRound = max(1, $announcements->sum(fn ($a) => mb_strlen($a->text) * 8 + 70));
        $repeat = max(1, (int) ceil(2400 / $oneRound));
        $items = collect(array_fill(0, $repeat, $announcements))->flatten(1);
        // hız sabit kalsın: kopya ne kadar uzunsa süre o kadar uzun (~70 px/sn)
        $duration = max(25, round($oneRound * $repeat / 70));
        $key = 'ts-announce-' . md5($announcements->map(fn ($a) => $a->id . $a->updated_at)->join('|'));
    @endphp

    {{-- Yazılar fosforlu sarı (#F2FF00), içlerinden ışık akar; üzerinden ara ara ışık şeridi geçer (app.css: announcement-bar) --}}
    {{-- Sayfa çizilmeden önce: kapatıldıysa şerit hiç görünmesin (gelip gitmesin), hero yüksekliği de buna göre --}}
    <script>
        (() => {
            let hidden = false;
            try { hidden = !!sessionStorage.getItem(@json($key)); } catch (e) {}
            document.documentElement.classList.toggle('bar-dismissed', hidden);
            document.documentElement.style.setProperty('--bar-h', hidden ? '0px' : '36px');
        })();
    </script>
    <div x-data="{
            open: true,
            init() { try { localStorage.removeItem('{{ $key }}'); this.open = !sessionStorage.getItem('{{ $key }}') } catch (e) {} this.$watch('open', () => this.sync()); this.sync() },
            dismiss() { this.open = false; try { sessionStorage.setItem('{{ $key }}', 1) } catch (e) {} },
            // Şeridin yüksekliği (açıkken 36px): hero ekranı tam doldururken bunu düşer; kapanınca boşluk kalmaz
            sync() { document.documentElement.style.setProperty('--bar-h', this.open ? this.$el.offsetHeight + 'px' : '0px') },
        }" x-show="open"
        class="announcement-bar relative bg-slate-900 text-white" role="region" aria-label="{{ __('announcement-bar.label') }}">

        <div class="overflow-hidden pr-10 rtl:pl-10 rtl:pr-0" dir="ltr">
            <div class="announcement-track flex w-max" style="--announcement-duration: {{ $duration }}s">
                @foreach ([0, 1] as $copy)
                    <ul class="flex shrink-0 items-center" @if ($copy) aria-hidden="true" @endif>
                        @foreach ($items as $announcement)
                            <li class="flex items-center h-9 text-[13px] font-semibold tracking-wide whitespace-nowrap">
                                <span class="mx-6 inline-block size-1.5 rounded-full bg-[#E04848]"></span>
                                @if ($announcement->url())
                                    <a href="{{ $announcement->url() }}" @if ($copy) tabindex="-1" @endif
                                        @if ($announcement->isExternal()) target="_blank" rel="noopener noreferrer" @endif
                                        class="announcement-glow hover:brightness-125 underline-offset-4 hover:underline">
                                        {{ $announcement->text }}
                                        <span aria-hidden="true" class="ml-1 text-[#E04848]">→</span>
                                    </a>
                                @else
                                    <span class="announcement-glow">{{ $announcement->text }}</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endforeach
            </div>
        </div>

        <button type="button" @click="dismiss()"
            aria-label="{{ __('announcement-bar.close') }}"
            class="absolute right-0 top-0 z-10 h-9 w-10 flex items-center justify-center bg-slate-900 text-white/60 hover:text-white transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24">
                <path d="M6 6l12 12M18 6L6 18" />
            </svg>
        </button>
    </div>
@endif
