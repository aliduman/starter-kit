<?php

namespace App\Support\Seo\Analysis\Checks;

use App\Support\Seo\Analysis\SeoCheck;
use App\Support\Seo\Analysis\SeoResult;
use App\Support\Seo\Analysis\SeoSubject;

/** Ateşman (1997) Türkçe okunabilirlik puanı: 90+ çok kolay, 70–89 kolay, 50–69 orta, 30–49 zor, 30 altı çok zor. */
class Readability extends SeoCheck
{
    private const MIN_WORDS = 100;

    public function run(SeoSubject $subject): ?SeoResult
    {
        $content = $subject->content();

        if (! $content || count($content->words) < self::MIN_WORDS) {
            return null;
        }

        $score = (int) round(max(0, min(100, $content->readability())));
        $label = match (true) {
            $score >= 90 => 'çok kolay',
            $score >= 70 => 'kolay',
            $score >= 50 => 'orta güçlükte',
            $score >= 30 => 'zor',
            default => 'çok zor',
        };
        $message = "Okunabilirlik (Ateşman): {$score}/100, {$label}.";

        return match (true) {
            $score >= 60 => $this->good($message),
            $score >= 40 => $this->ok("{$message} Cümleleri kısaltmak ve uzun kelimeleri azaltmak okumayı kolaylaştırır."),
            default => $this->bad("{$message} Cümleler çok uzun; bölerek kısaltın."),
        };
    }
}
