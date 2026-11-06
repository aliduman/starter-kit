<?php

namespace App\Support\Seo\Analysis\Checks;

use App\Support\Seo\Analysis\SeoCheck;
use App\Support\Seo\Analysis\SeoResult;
use App\Support\Seo\Analysis\SeoSubject;

class InternalLinks extends SeoCheck
{
    public function run(SeoSubject $subject): ?SeoResult
    {
        $content = $subject->content();

        if (! $content || $content->words === []) {
            return null;
        }

        return $content->internalLinks > 0
            ? $this->good("Metinde sitenin başka sayfalarına {$content->internalLinks} bağlantı var.")
            : $this->ok('Metinde sitenin başka sayfalarına bağlantı yok. İlgili içeriklere bağlantı verin.');
    }
}
