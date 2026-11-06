<?php

namespace App\Support\Seo\Analysis\Checks;

use App\Support\Seo\Analysis\SeoCheck;
use App\Support\Seo\Analysis\SeoResult;
use App\Support\Seo\Analysis\SeoSubject;

class SlugLength extends SeoCheck
{
    public function run(SeoSubject $subject): ?SeoResult
    {
        $length = mb_strlen((string) $subject->slug);
        $max = $this->setting('slug_max');

        return $length > $max
            ? $this->ok("Adres uzun ({$length} karakter). Kısa adresler arama sonucunda tam görünür.")
            : null;
    }
}
