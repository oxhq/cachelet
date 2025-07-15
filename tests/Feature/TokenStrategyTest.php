<?php

use Garaekz\Cachelet\Strategies\TokenGenerationStrategy;

it('returns default token value', function () {
    $s = new TokenGenerationStrategy();
    expect($s->getTokenFor('users', 1))->toBeInt();
});
