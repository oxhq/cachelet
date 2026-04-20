<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Cache;
use Oxhq\Cachelet\Facades\Cachelet;
use Oxhq\Cachelet\Query\Facades\CacheletQuery;
use Oxhq\Cachelet\Request\Facades\CacheletRequest;
use Tests\Models\Dummy;

it('installs the full suite through the meta package', function () {
    Cache::flush();

    $core = Cachelet::for('meta-suite')->from(['ok' => true])->remember(fn () => 'value');
    $query = Dummy::query()->cachelet();

    expect($core)->toBe('value')
        ->and($query->coordinate()->metadata['type'])->toBe('query')
        ->and(app()->bound('cachelet.request'))->toBeTrue()
        ->and(CacheletQuery::prefixFor('dummies'))->toBe('query:dummies')
        ->and(CacheletRequest::invalidatePrefix('missing'))->toBeArray();
});
