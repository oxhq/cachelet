<?php

declare(strict_types=1);

use Illuminate\Support\Carbon;
use Oxhq\Cachelet\ValueObjects\CacheScope;
use Tests\Models\Dummy;

it('ignores date fields by default when deriving model keys', function () {
    $first = new Dummy([
        'id' => 1,
        'name' => 'Alice',
        'email_verified_at' => Carbon::now()->addDay(),
        'created_at' => Carbon::now()->subDay(),
        'updated_at' => Carbon::now()->subHours(2),
    ]);

    $second = new Dummy([
        'id' => 1,
        'name' => 'Alice',
        'email_verified_at' => Carbon::now()->addDays(2),
        'created_at' => Carbon::now(),
        'updated_at' => Carbon::now()->addHour(),
    ]);

    expect($first->cachelet()->key())->toBe($second->cachelet()->key());
});

it('supports only and exclude filters for model payloads', function () {
    $model = new Dummy(['id' => 2, 'name' => 'Bob', 'role' => 'admin']);

    $base = $model->cachelet()->key();
    $excluded = $model->cachelet()->exclude(['role'])->key();
    $onlyId = $model->cachelet()->only(['id'])->key();
    $onlyIdChanged = (new Dummy(['id' => 2, 'name' => 'Bobby', 'role' => 'staff']))->cachelet()->only(['id'])->key();

    expect($excluded)->not->toBe($base)
        ->and($onlyId)->toBe($onlyIdChanged);
});

it('stamps model coordinates with a canonical module discriminator', function () {
    $model = new Dummy(['id' => 7, 'name' => 'Dora']);
    $coordinate = $model->cachelet()->coordinate();

    expect($coordinate->module)->toBe('model')
        ->and($coordinate->metadata)->toMatchArray([
            'module' => 'model',
            'type' => 'model',
            'model_class' => Dummy::class,
            'model_key' => 7,
        ]);
});

it('prefers an explicit scope over the inferred model scope', function () {
    $coordinate = (new Dummy(['id' => 7, 'name' => 'Dora']))
        ->cachelet()
        ->scope(CacheScope::named('catalog.users'))
        ->coordinate()
        ->toProjection();

    expect($coordinate['scope'])->toMatchArray([
        'contract' => 'cachelet.scope.v1',
        'identifier' => 'catalog.users',
        'source' => 'explicit',
    ]);
});

it('infers a stable scope from the model boundary', function () {
    $first = (new Dummy(['id' => 7, 'name' => 'Dora']))->cachelet()->coordinate()->toProjection();
    $second = (new Dummy(['id' => 8, 'name' => 'Nora']))->cachelet()->coordinate()->toProjection();

    expect($first['scope']['source'])->toBe('inferred')
        ->and($second['scope']['source'])->toBe('inferred')
        ->and($first['scope']['identifier'])->not->toBe('')
        ->and($first['scope']['identifier'])->toBe($second['scope']['identifier']);
});

it('can opt dates and timestamps into the model payload', function () {
    $first = new Dummy([
        'id' => 3,
        'name' => 'Carol',
        'email_verified_at' => Carbon::now()->addDay(),
        'created_at' => Carbon::now()->subDay(),
        'updated_at' => Carbon::now()->subDay(),
    ]);

    $second = new Dummy([
        'id' => 3,
        'name' => 'Carol',
        'email_verified_at' => Carbon::now()->addDays(3),
        'created_at' => Carbon::now(),
        'updated_at' => Carbon::now(),
    ]);

    expect($first->cachelet()->withDates(['email_verified_at'])->key())
        ->not->toBe($second->cachelet()->withDates(['email_verified_at'])->key())
        ->and($first->cachelet()->withTimestamps()->key())
        ->not->toBe($second->cachelet()->withTimestamps()->key());
});
