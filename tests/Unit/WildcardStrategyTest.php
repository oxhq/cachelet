<?php

use Garaekz\Cachelet\Strategies\WildcardInvalidationStrategy;
use Garaekz\Cachelet\ValueObjects\CacheletDefinition;

it('skips invalid drivers safely', function () {
    $s = new WildcardInvalidationStrategy();
    $def = new CacheletDefinition(context: [], ttl: 60, tags: [], metadata: [], resolver: fn() => null);
    $s->invalidate($def);
    expect(true)->toBeTrue();
});
