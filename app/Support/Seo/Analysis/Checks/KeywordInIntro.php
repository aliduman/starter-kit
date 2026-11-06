<?php

namespace App\Support\Seo\Analysis\Checks;

use App\Support\Seo\Analysis\RequiresKeyword;
use App\Support\Seo\Analysis\SeoCheck;
use App\Support\Seo\Analysis\SeoResult;
use App\Support\Seo\Analysis\SeoSubject;
use App\Support\Seo\Analysis\SeoText;

class KeywordInIntro extends SeoCheck implements RequiresKeyword
{
    public function applies(SeoSubject $subject): bool
    {
        return filled($subject->content()?->intro);
    }

    public function run(SeoSubject $subject): ?SeoResult
    {
        if (! $subject->keyword || ! $this->applies($subject)) {
            return null;
        }

        $intro = $subject->content()->intro;

        return SeoText::contains($intro, $subject->keyword)
            ? $this->good('Anahtar kelime ilk paragrafta geçiyor.')
            : $this->bad('Anahtar kelime ilk paragrafta geçmiyor. Konuyu ilk paragrafta adıyla anın.');
    }
}
