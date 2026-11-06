<?php

namespace App\Support\Seo\Analysis\Checks;

use App\Support\Seo\Analysis\RequiresKeyword;
use App\Support\Seo\Analysis\SeoCheck;
use App\Support\Seo\Analysis\SeoResult;
use App\Support\Seo\Analysis\SeoSubject;

class KeywordUnique extends SeoCheck implements RequiresKeyword
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

        $uses = $subject->keywordUses();

        return $uses > 0
            ? $this->ok("Bu odak anahtar kelime {$uses} başka içerikte de kullanılmış. Aynı arama için yarışmamaları için farklı bir ifade seçin.")
            : $this->good('Bu odak anahtar kelime başka bir içerikte kullanılmamış.');
    }
}
