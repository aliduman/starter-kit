<?php

namespace App\Support\Seo\Analysis\Checks;

use App\Support\Seo\Analysis\RequiresKeyword;
use App\Support\Seo\Analysis\SeoCheck;
use App\Support\Seo\Analysis\SeoResult;
use App\Support\Seo\Analysis\SeoSubject;
use Illuminate\Support\Str;

class KeywordInSlug extends SeoCheck implements RequiresKeyword
{
    public function applies(SeoSubject $subject): bool
    {
        return filled($subject->slug);
    }

    public function run(SeoSubject $subject): ?SeoResult
    {
        if (! $subject->keyword || ! $this->applies($subject)) {
            return null;
        }

        return str_contains($subject->slug, Str::slug($subject->keyword))
            ? $this->good('Anahtar kelime adreste (URL) geçiyor.')
            : $this->ok('Anahtar kelime adreste (URL) geçmiyor. Yayındaki içeriğin adresini değiştirmek eski bağlantıları bozar; yeni içerikte düzeltin.');
    }
}
