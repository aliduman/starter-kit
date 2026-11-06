<?php

namespace App\Support\Seo\Analysis;

final class SeoReport
{
    private const POINTS = [SeoResult::GOOD => 1, SeoResult::OK => 0.5, SeoResult::BAD => 0];

    private const ORDER = [SeoResult::BAD => 0, SeoResult::OK => 1, SeoResult::GOOD => 2];

    /** @var list<SeoResult> Önce sorunlar, sonra iyileştirmeler, en son iyi olanlar. */
    public readonly array $results;

    /**
     * @param  list<SeoResult>  $results
     * @param  int  $missed  Anahtar kelime girilmediği için çalışmayan, sıfır puan sayılan denetim sayısı.
     */
    public function __construct(array $results, private readonly int $missed = 0)
    {
        usort($results, fn (SeoResult $a, SeoResult $b) => self::ORDER[$a->status] <=> self::ORDER[$b->status]);

        $this->results = $results;
    }

    /** 0–100: iyi sonuç tam, iyileştirilebilir yarım puan. */
    public function score(): int
    {
        $total = count($this->results) + $this->missed;

        if ($total === 0) {
            return 0;
        }

        $points = array_sum(array_map(fn (SeoResult $result) => self::POINTS[$result->status], $this->results));

        return (int) round(100 * $points / $total);
    }

    public function status(): string
    {
        return self::statusFor($this->score());
    }

    public static function statusFor(int $score): string
    {
        $thresholds = config('seo.analysis.score', ['good' => 75, 'ok' => 45]);

        return match (true) {
            $score >= $thresholds['good'] => SeoResult::GOOD,
            $score >= $thresholds['ok'] => SeoResult::OK,
            default => SeoResult::BAD,
        };
    }
}
