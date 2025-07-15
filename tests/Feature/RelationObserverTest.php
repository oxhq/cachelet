<?php

use Garaekz\Cachelet\Observers\RelationObserver;

it('registers relation observer without error', function () {
    RelationObserver::register();
    expect(true)->toBeTrue(); // basic smoke test
});
