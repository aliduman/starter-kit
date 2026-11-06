<?php

namespace App\Support\Seo\Analysis\Checks;

use App\Support\Seo\Analysis\SeoCheck;
use App\Support\Seo\Analysis\SeoResult;
use App\Support\Seo\Analysis\SeoSubject;

class ContentLength extends SeoCheck
{
    public function run(SeoSubject $subject): ?SeoResult
    {
        $content = $subject->content();

        if (! $content) {
            return null;
        }

        $words = count($content->words);
        $min = $this->setting('min_words');

        return match (true) {
            $words === 0 => $this->bad('İçerik metni boş.'),
            $words < $min / 2 => $this->bad("Metin çok kısa ({$words} kelime). En az {$min} kelime önerilir."),
            $words < $min => $this->ok("Metin biraz kısa ({$words} kelime). En az {$min} kelime önerilir."),
            default => $this->good("Metin uzunluğu iyi ({$words} kelime)."),
        };
    }
}
