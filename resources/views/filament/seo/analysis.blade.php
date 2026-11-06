{{-- SEO analiz paneli (App\Filament\Seo\SeoSection). Panelin derlenmiş CSS'inde olmayan sınıflar kullanılmaz; stil burada. --}}
@php
    $labels = ['good' => 'İyi', 'ok' => 'İyileştirilebilir', 'bad' => 'Sorunlu'];
@endphp

<div class="seo-analysis">
    <style>
        .seo-analysis { --seo-good: #16a34a; --seo-ok: #ea8c00; --seo-bad: #dc2626; --seo-muted: #6b7280; --seo-border: #e5e7eb; --seo-card: #fff; display: grid; gap: 16px; }
        .dark .seo-analysis { --seo-muted: #9ca3af; --seo-border: rgb(255 255 255 / .1); --seo-card: rgb(255 255 255 / .03); }
        .seo-analysis__head { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; }
        .seo-analysis__score { display: inline-flex; align-items: center; gap: 10px; font-weight: 600; }
        .seo-analysis__badge { min-width: 52px; padding: 4px 10px; border-radius: 999px; color: #fff; text-align: center; font-variant-numeric: tabular-nums; }
        .seo-analysis__refresh { font-size: 13px; color: var(--seo-muted); text-decoration: underline; cursor: pointer; background: none; border: 0; }
        .seo-analysis__serp { max-width: 600px; padding: 14px 16px; border: 1px solid var(--seo-border); border-radius: 10px; background: var(--seo-card); font-family: Arial, sans-serif; }
        .seo-analysis__serp-label { margin-bottom: 8px; font-size: 12px; color: var(--seo-muted); font-family: inherit; }
        .seo-analysis__url { font-size: 12px; color: #4d5156; overflow: hidden; white-space: nowrap; text-overflow: ellipsis; }
        .dark .seo-analysis__url { color: #bdc1c6; }
        /* Google başlığı piksel genişliğiyle keser: 20px Arial, ~600px. */
        .seo-analysis__title { margin: 4px 0 3px; font-size: 20px; line-height: 1.3; color: #1a0dab; overflow: hidden; white-space: nowrap; text-overflow: ellipsis; }
        .dark .seo-analysis__title { color: #99c3ff; }
        .seo-analysis__desc { font-size: 14px; line-height: 1.58; color: #4d5156; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .dark .seo-analysis__desc { color: #bdc1c6; }
        .seo-analysis__desc--empty { font-style: italic; color: var(--seo-muted); }
        .seo-analysis__list { display: grid; gap: 8px; margin: 0; padding: 0; list-style: none; }
        .seo-analysis__item { display: flex; gap: 10px; align-items: baseline; font-size: 14px; line-height: 1.5; }
        .seo-analysis__dot { flex: none; width: 10px; height: 10px; border-radius: 50%; transform: translateY(1px); }
        .seo-analysis--good { background: var(--seo-good); }
        .seo-analysis--ok { background: var(--seo-ok); }
        .seo-analysis--bad { background: var(--seo-bad); }
    </style>

    @if (! $report)
        <p style="color: var(--seo-muted)">SEO analizi şu an yapılamadı. Kaydedip sayfayı yeniden açın.</p>
    @else
        <div class="seo-analysis__head">
            <div class="seo-analysis__score">
                <span class="seo-analysis__badge seo-analysis--{{ $report->status() }}">{{ $report->score() }}</span>
                <span>SEO puanı: {{ $labels[$report->status()] }}</span>
            </div>
            <button type="button" class="seo-analysis__refresh" wire:click="$refresh">Analizi yenile</button>
        </div>

        <div class="seo-analysis__serp">
            <div class="seo-analysis__serp-label">Google önizlemesi</div>
            <div class="seo-analysis__url">{{ $preview['url'] }}</div>
            <div class="seo-analysis__title" title="{{ $preview['title'] }}">{{ $preview['title'] }}</div>
            @if ($preview['description'])
                <div class="seo-analysis__desc">{{ $preview['description'] }}</div>
            @else
                <div class="seo-analysis__desc seo-analysis__desc--empty">Açıklama yok; Google sayfadan kendisi bir parça seçer.</div>
            @endif
        </div>

        <ul class="seo-analysis__list">
            @foreach ($report->results as $result)
                <li class="seo-analysis__item">
                    <span class="seo-analysis__dot seo-analysis--{{ $result->status }}" title="{{ $labels[$result->status] }}"></span>
                    <span>{{ $result->message }}</span>
                </li>
            @endforeach
        </ul>
    @endif
</div>
