<?php

/*
 * OPcache preloader — loaded once per worker process.
 * Configure via opcache.preload in php.ini / opcache.ini.
 */

if (! file_exists(__DIR__.'/vendor/autoload.php')) {
    return;
}

require __DIR__.'/vendor/autoload.php';

// Preload Laravel framework + commonly hit classes here as the project grows.
// Keeping this minimal avoids stale-class issues during deploys.
