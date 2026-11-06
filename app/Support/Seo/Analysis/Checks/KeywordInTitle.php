<?php

namespace App\Support\Seo\Analysis\Checks;

use App\Support\Seo\Analysis\RequiresKeyword;
use App\Support\Seo\Analysis\SeoCheck;
use App\Support\Seo\Analysis\SeoResult;
use App\Support\Seo\Analysis\SeoSubject;
use App\Support\Seo\Analysis\SeoText;

class KeywordInTitle extends SeoCheck implements RequiresKeyword
{
    public function applies(SeoSubject $subject): bool
    {
        return true;
    }

    public function run(SeoSubject $subject): ?SeoResult
    {
        if (! $subject->keyword || ! $this->applies($subject)) {
            return null;
        }

        return SeoText::contains($subject->title, $subject->keyword)
            ? $this->good('Anahtar kelime başlıkta geçiyor.')
            : $this->bad('Anahtar kelime başlıkta geçmiyor. Başlığa ya da meta başlığa ekleyin.');
    }
}
