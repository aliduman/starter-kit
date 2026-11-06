<?php

namespace App\Support\Seo\Analysis\Checks;

use App\Support\Seo\Analysis\SeoCheck;
use App\Support\Seo\Analysis\SeoResult;
use App\Support\Seo\Analysis\SeoSubject;

class DescriptionLength extends SeoCheck
{
    public function run(SeoSubject $subject): ?SeoResult
    {
        $description = $subject->plainDescription();
        ['min' => $min, 'max' => $max] = $this->setting('description');

        if (! $description) {
            return $this->bad("Arama sonucunda gösterilecek açıklama yok. {$min}–{$max} karakterlik bir meta açıklama yazın.");
        }

        if ($subject->descriptionIsAuto) {
            return $this->ok('Meta açıklama boş; yerine özet ya da metnin başı kullanılıyor. İçeriği tanıtan bir meta açıklama yazın.');
        }

        $length = mb_strlen($description);

        return match (true) {
            $length > $max => $this->ok("Meta açıklama uzun ({$length} karakter); {$max} karakterden sonrası kesilir."),
            $length < $min => $this->ok("Meta açıklama kısa ({$length} karakter). {$min}–{$max} karakter önerilir."),
            default => $this->good("Meta açıklama uzunluğu iyi ({$length} karakter)."),
        };
    }
}
