<?php

namespace App\Support\Seo\Analysis\Checks;

use App\Support\Seo\Analysis\SeoCheck;
use App\Support\Seo\Analysis\SeoResult;
use App\Support\Seo\Analysis\SeoSubject;

class ExternalLinks extends SeoCheck
{
    public function run(SeoSubject $subject): ?SeoResult
    {
        $content = $subject->content();

        if (! $content || $content->words === []) {
            return null;
        }

        return $content->externalLinks > 0
            ? $this->good("Dış kaynaklara {$content->externalLinks} bağlantı var.")
            : $this->ok('Dış kaynaklara bağlantı yok. Güvenilir kaynaklara bağlantı vermek içeriğe güven katar.');
    }
}
