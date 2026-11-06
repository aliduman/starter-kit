<?php

namespace App\Support\Seo\Analysis\Checks;

use App\Support\Seo\Analysis\RequiresKeyword;
use App\Support\Seo\Analysis\SeoCheck;
use App\Support\Seo\Analysis\SeoResult;
use App\Support\Seo\Analysis\SeoSubject;
use App\Support\Seo\Analysis\SeoText;

class KeywordInDescription extends SeoCheck implements RequiresKeyword
{
    public function applies(SeoSubject $subject): bool
    {
        return $subject->plainDescription() !== null;
    }

    public function run(SeoSubject $subject): ?SeoResult
    {
        if (! $subject->keyword || ! $this->applies($subject)) {
            return null;
        }

        return SeoText::contains($subject->plainDescription(), $subject->keyword)
            ? $this->good('Anahtar kelime açıklamada geçiyor.')
            : $this->ok('Anahtar kelime açıklamada geçmiyor. Google aranan kelimeyi açıklamada kalın gösterir.');
    }
}
