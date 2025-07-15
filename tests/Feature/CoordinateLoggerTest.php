<?php

use Garaekz\Cachelet\Core\CoordinateLogger;
use Garaekz\Cachelet\ValueObjects\CacheCoordinate;

it('logs and inspects coordinates', function () {
    $c = CacheCoordinate::make('cachelet:foo:bar', 60, ['x'], ['meta']);
    $logger = new CoordinateLogger();
    $logger->log($c);
    $result = $logger->inspect('foo');
    expect($result)->toBeArray();
});
