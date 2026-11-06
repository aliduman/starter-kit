<?php

namespace App\Support\Seo\Analysis\Checks;

use App\Support\Seo\Analysis\RequiresKeyword;
use App\Support\Seo\Analysis\SeoCheck;
use App\Support\Seo\Analysis\SeoResult;
use App\Support\Seo\Analysis\SeoSubject;
use App\Support\Seo\Analysis\SeoText;

class KeywordDensity extends SeoCheck implements RequiresKeyword
{
    public function applies(SeoSubject $subject): bool
    {
        return ($subject->content()?->words ?? []) !== [];
    }

    public function run(SeoSubject $subject): ?SeoResult
    {
        if (! $subject->keyword || ! $this->applies($subject)) {
            return null;
        }

        $content = $subject->content();

        $count = SeoText::occurrences($content->text, $subject->keyword);
        $density = 100 * $count / count($content->words);
        ['min' => $min, 'max' => $max] = $this->setting('keyword_density');
        $stats = "{$count} kez, ".self::percent($density);

        return match (true) {
            $count === 0 => $this->bad('Anahtar kelime metinde hiç geçmiyor.'),
            $density > $max => $this->bad("Anahtar kelime çok sık geçiyor ({$stats}). Doğal olmayan tekrar arama motorlarınca cezalandırılabilir."),
            $density < $min => $this->ok("Anahtar kelime metinde az geçiyor ({$stats}). ".self::percent($min).'–'.self::percent($max).' arası önerilir.'),
            default => $this->good("Anahtar kelime metinde yeterince geçiyor ({$stats})."),
        };
    }
}
