<?php

use function Pest\Laravel\get;

it('renders the home page with module list', function () {
    get('/')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Home')
            ->has('modules')
        );
});
