<?php

use Garaekz\Cachelet\Core\InvalidationOrchestrator;
use Garaekz\Cachelet\ValueObjects\CacheletDefinition;

it('does not crash when invalidating', function () {
    $o = new InvalidationOrchestrator();
    $def = new CacheletDefinition(context: [], ttl: 60, tags: [], metadata: [], resolver: fn() => null);
    $o->invalidate($def);
    expect(true)->toBeTrue();
});
