<?php

namespace App\Support\Seo\Analysis\Checks;

use App\Support\Seo\Analysis\SeoCheck;
use App\Support\Seo\Analysis\SeoResult;
use App\Support\Seo\Analysis\SeoSubject;

class ImageAlt extends SeoCheck
{
    public function run(SeoSubject $subject): ?SeoResult
    {
        $content = $subject->content();

        if (! $content || $content->images === 0) {
            return null;
        }

        return $content->imagesWithoutAlt > 0
            ? $this->ok("Metindeki {$content->imagesWithoutAlt} görselin alternatif metni (alt) yok. Görseli anlatan kısa bir metin ekleyin.")
            : $this->good('Metindeki görsellerin alternatif metni var.');
    }
}
