<?php

namespace App\Support\Seo\Analysis;

final class SeoResult
{
    public const GOOD = 'good';

    public const OK = 'ok';

    public const BAD = 'bad';

    public function __construct(
        public readonly string $status,
        public readonly string $message,
    ) {}
}
