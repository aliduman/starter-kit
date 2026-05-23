<?php

use function Pest\Laravel\get;

it('disables a module via config', function () {
    config()->set('modules.list.media.enabled', false);

    get('/haber')->assertNotFound();
});
