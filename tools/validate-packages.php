<?php

declare(strict_types=1);

$packages = [
    'packages/cachelet-core',
    'packages/cachelet-model',
    'packages/cachelet-query',
    'packages/cachelet-request',
];

foreach ($packages as $package) {
    passthru('composer validate --strict --working-dir '.escapeshellarg(__DIR__.'/../'.$package), $exitCode);

    if ($exitCode !== 0) {
        exit($exitCode);
    }
}
