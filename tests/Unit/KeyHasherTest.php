<?php

use Garaekz\Cachelet\Core\KeyHasher;
use Garaekz\Cachelet\ValueObjects\CacheletDefinition;

it('generates deterministic keys', function () {
    $hasher = new KeyHasher;
    $def = new CacheletDefinition(context: ['x' => 1], ttl: 60, tags: [], metadata: [], resolver: fn () => null, prefix: null);
    expect($hasher->make($def))->toStartWith('cachelet:');
});

it('generates different keys for different contexts', function () {
    $hasher = new KeyHasher;
    $def1 = new CacheletDefinition(context: ['x' => 1], ttl: 60, tags: [], metadata: [], resolver: fn () => null, prefix: null);
    $def2 = new CacheletDefinition(context: ['x' => 2], ttl: 60, tags: [], metadata: [], resolver: fn () => null, prefix: null);

    expect($hasher->make($def1))->not->toBe($hasher->make($def2));
});

it('generates keys in a predictable way', function () {
    $hasher = new KeyHasher;
    $def1 = new CacheletDefinition(context: ['x' => 1], ttl: 60, tags: [], metadata: [], resolver: fn () => null, prefix: null);
    $def2 = new CacheletDefinition(context: ['x' => 1], ttl: 60, tags: [], metadata: [], resolver: fn () => null, prefix: null);

    expect($hasher->make($def1))->toBe($hasher->make($def2));
});
