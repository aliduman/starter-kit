<?php

namespace App\Support\Seo\Analysis;

/** Tek bir SEO denetimi. İçeriğe uymuyorsa (ör. anahtar kelime yok, metin yok) null döner ve puana girmez. */
abstract class SeoCheck
{
    abstract public function run(SeoSubject $subject): ?SeoResult;

    protected function good(string $message): SeoResult
    {
        return new SeoResult(SeoResult::GOOD, $message);
    }

    protected function ok(string $message): SeoResult
    {
        return new SeoResult(SeoResult::OK, $message);
    }

    protected function bad(string $message): SeoResult
    {
        return new SeoResult(SeoResult::BAD, $message);
    }

    protected function setting(string $key): mixed
    {
        return config("seo.analysis.{$key}");
    }

    protected static function percent(float $value): string
    {
        return '%'.number_format($value, 1, ',', '');
    }
}
