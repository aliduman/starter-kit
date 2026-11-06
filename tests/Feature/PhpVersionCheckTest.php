<?php

use App\Support\PhpVersionCheck;

beforeEach(function () {
    config(['app.php_security_minimums' => ['8.4' => '8.4.27', '8.5' => '8.5.12']]);
});

it('accepts patched versions', function (string $version) {
    expect(PhpVersionCheck::requiredFor($version))->toBeNull()
        ->and(PhpVersionCheck::report($version))->toContain('güvenlik sürümü tamam');
})->with(['8.4.27', '8.4.30', '8.5.12', '8.6.0']);

it('requires the branch minimum for unpatched versions', function (string $version, string $required) {
    expect(PhpVersionCheck::requiredFor($version))->toBe($required)
        ->and(PhpVersionCheck::report($version))->toContain('UYARI')->toContain($required);
})->with([
    ['8.4.26', '8.4.27'],
    ['8.5.11', '8.5.12'],
    ['8.3.99', '8.4.27'], // unlisted older branch
]);

it('warns about nothing when the list is empty', function () {
    config(['app.php_security_minimums' => []]);

    expect(PhpVersionCheck::requiredFor('8.4.0'))->toBeNull();
});
