<?php

namespace App\Support\Seo\Analysis\Checks;

use App\Support\Seo\Analysis\SeoCheck;
use App\Support\Seo\Analysis\SeoResult;
use App\Support\Seo\Analysis\SeoSubject;

class CoverImage extends SeoCheck
{
    public function run(SeoSubject $subject): ?SeoResult
    {
        if (! $subject->image) {
            return $subject->expectsImage
                ? $this->bad('Kapak görseli yok. Listelerde ve sosyal medya paylaşımlarında görsel boş görünür.')
                : null;
        }

        ['width' => $width, 'height' => $height] = $subject->image;

        if (! $width || ! $height) {
            return null;
        }

        ['min_width' => $minWidth, 'ratio' => $ratio, 'tolerance' => $tolerance] = $this->setting('image');
        $size = "{$width}×{$height}";
        $recommended = $this->setting('image.recommended');

        $small = $width < $minWidth;
        $cropped = abs($width / $height - $ratio) > $ratio * $tolerance;

        return match (true) {
            $small && $cropped => $this->ok("Kapak görseli küçük ve oranı uygun değil ({$size}); bulanık görünür ve kenarlarından kırpılır. Önerilen ölçü {$recommended}, önemli kısımlar ortada."),
            $small => $this->ok("Kapak görseli küçük ({$size}); büyük ekranda bulanık görünür. Önerilen ölçü {$recommended}."),
            $cropped => $this->ok("Kapak görselinin oranı uygun değil ({$size}); sitede kenarlarından kırpılır. Önerilen ölçü {$recommended}, önemli kısımlar ortada."),
            default => $this->good("Kapak görseli uygun ({$size})."),
        };
    }
}
