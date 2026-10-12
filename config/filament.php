<?php

/*
 * Only overrides; Filament merges its remaining defaults from vendor/filament/support/config/filament.php.
 * Filament 4+ falls back to Laravel's private 'local' disk, so panel uploads got no public URL. Site media is
 * meant to be served from the public disk.
 */
return [
    'default_filesystem_disk' => env('FILAMENT_FILESYSTEM_DISK', 'public'),

    // Admin panel URL path (not a Filament key). Read by AdminPanelProvider and by the routes that must not treat the
    // panel as site content (App\Support\Routing\ContentPaths::systemPrefixes()). Kept in config so config:cache works.
    'path' => env('FILAMENT_PATH', 'admin'),
];
