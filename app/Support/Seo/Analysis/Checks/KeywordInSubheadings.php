<?php

namespace App\Support\Seo\Analysis\Checks;

use App\Support\Seo\Analysis\RequiresKeyword;
use App\Support\Seo\Analysis\SeoCheck;
use App\Support\Seo\Analysis\SeoResult;
use App\Support\Seo\Analysis\SeoSubject;
use App\Support\Seo\Analysis\SeoText;

class KeywordInSubheadings extends SeoCheck implements RequiresKeyword
{
    public function applies(SeoSubject $subject): bool
    {
        return ($subject->content()?->headings ?? []) !== [];
    }

    public function run(SeoSubject $subject): ?SeoResult
    {
        if (! $subject->keyword || ! $this->applies($subject)) {
            return null;
        }

        $headings = $subject->content()->headings;

        $matches = count(array_filter($headings, fn (string $heading) => SeoText::contains($heading, $subject->keyword)));

        return $matches > 0
            ? $this->good("Anahtar kelime {$matches} alt başlıkta geçiyor.")
            : $this->ok('Anahtar kelime hiçbir alt başlıkta geçmiyor. Uygun bir alt başlıkta kullanın.');
    }
}
