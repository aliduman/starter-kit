<?php

// Only overridden keys live here; the package config fills the remaining top-level keys.
// Nested arrays are merged shallowly, so each overridden group must be complete.
return [

    // The package default is lowercase `js/pages`; this project uses `js/Pages`. macOS hides the difference
    // (case-insensitive), Linux CI does not: assertInertia()->component() fails to find the page files.
    'pages' => [

        'ensure_pages_exist' => false,

        'paths' => [
            resource_path('js/Pages'),
        ],

        'extensions' => [
            'js',
            'jsx',
            'svelte',
            'ts',
            'tsx',
            'vue',
        ],

    ],

];
