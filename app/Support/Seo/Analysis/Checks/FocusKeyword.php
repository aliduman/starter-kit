<?php

namespace App\Support\Seo\Analysis\Checks;

use App\Support\Seo\Analysis\SeoCheck;
use App\Support\Seo\Analysis\SeoResult;
use App\Support\Seo\Analysis\SeoSubject;
use App\Support\Seo\Analysis\SeoText;

class FocusKeyword extends SeoCheck
{
    public function run(SeoSubject $subject): ?SeoResult
    {
        if (! $subject->keyword) {
            return $this->bad('Odak anahtar kelime girilmedi. İçeriğin hangi aramada çıkmasını istediğinizi yazın; anahtar kelime denetimleri ona göre çalışır ve puana girer.');
        }

        $words = count(SeoText::words($subject->keyword));

        return $words > 4
            ? $this->ok("Odak anahtar kelime uzun ({$words} kelime). Arama yapanların yazacağı 1–4 kelimelik bir ifade seçin.")
            : null;
    }
}
