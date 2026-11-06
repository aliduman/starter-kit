<?php

namespace App\Support\Seo\Analysis\Checks;

use App\Support\Seo\Analysis\SeoCheck;
use App\Support\Seo\Analysis\SeoResult;
use App\Support\Seo\Analysis\SeoSubject;

class TitleLength extends SeoCheck
{
    public function run(SeoSubject $subject): ?SeoResult
    {
        $length = mb_strlen($subject->title);
        ['min' => $min, 'max' => $max] = $this->setting('title');

        return match (true) {
            $length > $max + 10 => $this->bad("Başlık çok uzun ({$length} karakter). Google yaklaşık {$max} karakterden sonrasını keser."),
            $length > $max => $this->ok("Başlık biraz uzun ({$length} karakter); sonu kesilebilir. {$max} karakterin altında tutun."),
            $length < $min => $this->ok("Başlık kısa ({$length} karakter). {$min}–{$max} karakter arası daha iyi sonuç verir."),
            default => $this->good("Başlık uzunluğu iyi ({$length} karakter)."),
        };
    }
}
