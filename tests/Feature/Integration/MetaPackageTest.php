<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Oxhq\Cachelet\Cloud\Exporting\TelemetryExporter;
use Oxhq\Cachelet\Facades\Cachelet;
use Oxhq\Cachelet\Query\Facades\CacheletQuery;
use Oxhq\Cachelet\Request\Facades\CacheletRequest;
use Tests\Models\Dummy;

it('installs the full suite through the meta package', function () {
    Cache::flush();

    $core = Cachelet::for('meta-suite')->from(['ok' => true])->remember(fn () => 'value');
    $model = Dummy::create(['name' => 'Alice'])->cachelet();
    $query = Dummy::query()->cachelet();
    $request = app('cachelet.request')->for(Request::create('/users', 'GET'), 'users');

    expect($core)->toBe('value')
        ->and($model->coordinate()->module)->toBe('model')
        ->and($model->coordinate()->metadata['type'])->toBe('model')
        ->and($query->coordinate()->metadata['type'])->toBe('query')
        ->and($query->coordinate()->module)->toBe('query')
        ->and($request->builder()->coordinate()->module)->toBe('request')
        ->and($request->builder()->coordinate()->metadata['type'])->toBe('request')
        ->and(app()->bound('cachelet.request'))->toBeTrue()
        ->and(app()->bound(TelemetryExporter::class))->toBeTrue()
        ->and(CacheletQuery::prefixFor('dummies'))->toBe('query:dummies')
        ->and(CacheletRequest::invalidatePrefix('missing'))->toBeArray();
});
