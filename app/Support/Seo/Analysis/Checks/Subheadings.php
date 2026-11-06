<?php

namespace App\Support\Seo\Analysis\Checks;

use App\Support\Seo\Analysis\SeoCheck;
use App\Support\Seo\Analysis\SeoResult;
use App\Support\Seo\Analysis\SeoSubject;

class Subheadings extends SeoCheck
{
    public function run(SeoSubject $subject): ?SeoResult
    {
        $content = $subject->content();

        if (! $content || count($content->words) < $this->setting('min_words')) {
            return null;
        }

        return $content->headings === []
            ? $this->ok('Uzun metinde alt başlık yok. Bölümlere alt başlık (Başlık 2/3) koymak okumayı kolaylaştırır.')
            : $this->good('Metin alt başlıklarla bölümlenmiş.');
    }
}
