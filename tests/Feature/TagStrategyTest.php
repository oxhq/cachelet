<?php

use Garaekz\Cachelet\Strategies\TagInvalidationStrategy;
use Garaekz\Cachelet\ValueObjects\CacheletDefinition;

it('ignores non-tagged drivers gracefully', function () {
    $s = new TagInvalidationStrategy;
    $def = new CacheletDefinition(context: [], ttl: 60, tags: ['x'], metadata: [], resolver: fn () => null, prefix: null);
    $s->invalidate($def);
    expect(true)->toBeTrue(); // just not crash
});
