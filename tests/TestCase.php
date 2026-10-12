<?php

namespace Tests;

use App\Support\Routing\TrailingSlash;
use App\Support\Routing\UrlGenerator;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Uri;

abstract class TestCase extends BaseTestCase
{
    /** Laravel test isteği sondaki "/" işaretini siler; verilen adres (bkz. TrailingSlash) aynen gitsin. */
    protected function prepareUrlForRequest($uri)
    {
        $url = parent::prepareUrlForRequest($uri);

        return UrlGenerator::endsWithSlash($uri instanceof Uri ? $uri->value() : $uri) ? TrailingSlash::append($url) : $url;
    }
}
