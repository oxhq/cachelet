<?php

use Garaekz\Cachelet\Core\CacheletBuilder;

it('can build definitions', function () {
    $def = (new CacheletBuilder())
        ->from(['user_id' => 1])
        ->ttl('15 minutes')
        ->remember(fn() => 'foo');

    expect($def)->toBe('foo');
});
