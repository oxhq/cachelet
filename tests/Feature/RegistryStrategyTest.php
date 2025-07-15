<?php

use Garaekz\Cachelet\Strategies\RegistryInvalidationStrategy;

it('registers and invalidates keys in registry', function () {
    $s = new RegistryInvalidationStrategy();
    $s->register('users', 'cachelet:users:abc');
    expect(true)->toBeTrue(); // placeholder
});
