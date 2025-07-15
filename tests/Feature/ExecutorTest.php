<?php

use Garaekz\Cachelet\Core\CacheletExecutor;
use Garaekz\Cachelet\Core\TtlParser;
use Garaekz\Cachelet\Core\KeyHasher;
use Garaekz\Cachelet\ValueObjects\CacheletDefinition;

it('executes and caches', function () {
    $exe = new CacheletExecutor(new KeyHasher(), new TtlParser());
    $def = new CacheletDefinition(context: [], ttl: 60, tags: [], metadata: [], resolver: fn() => 'bar');
    expect($exe->handle($def))->toBe('bar');
});
