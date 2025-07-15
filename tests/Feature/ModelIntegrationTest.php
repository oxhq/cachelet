<?php

declare(strict_types=1);

use Garaekz\Cachelet\ModelCachelet;
use Garaekz\Cachelet\Tests\TestCase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Tests\Models\Dummy;

uses(TestCase::class);

beforeEach(function () {
    Cache::flush();
});

it('ignores date fields by default', function () {
    $m1 = new Dummy([
        'id' => 1,
        'name' => 'Alice',
        'email_verified_at' => Carbon::now()->addDay(),
        'created_at' => Carbon::now()->subDay(),
        'updated_at' => Carbon::now()->subHours(2),
    ]);
    $m2 = new Dummy([
        'id' => 1,
        'name' => 'Alice',
        'email_verified_at' => Carbon::now()->addHours(5),
        'created_at' => Carbon::now()->subMinutes(30),
        'updated_at' => Carbon::now()->subMinutes(10),
    ]);

    $k1 = ModelCachelet::forModel($m1)->build()->key();
    $k2 = ModelCachelet::forModel($m2)->build()->key();

    expect($k1)->toBe($k2);
});

it('exclude() changes the payload and thus the key', function () {
    $m = new Dummy(['id' => 2, 'name' => 'Bob', 'role' => 'admin']);

    $k1 = ModelCachelet::forModel($m)->build()->key();
    $k2 = ModelCachelet::forModel($m)
        ->exclude(['role'])
        ->build()
        ->key();

    expect($k1)->not->toBe($k2);
});

it('only() restricts to the specified fields', function () {
    $m1 = new Dummy(['id' => 3, 'name' => 'Carol', 'role' => 'editor']);
    $m2 = new Dummy(['id' => 3, 'name' => 'Carol', 'role' => 'subscriber']);

    $k1 = ModelCachelet::forModel($m1)
        ->only(['id'])
        ->build()
        ->key();
    $k2 = ModelCachelet::forModel($m2)
        ->only(['id'])
        ->build()
        ->key();

    expect($k1)->toBe($k2);
});

it('withDates() includes the specified date field', function () {
    $dt1 = Carbon::now()->addDay();
    $dt2 = Carbon::now()->addDays(2);

    $m1 = new Dummy(['id' => 4, 'name' => 'Dave', 'email_verified_at' => $dt1]);
    $m2 = new Dummy(['id' => 4, 'name' => 'Dave', 'email_verified_at' => $dt2]);

    $k1 = ModelCachelet::forModel($m1)
        ->withDates(['email_verified_at'])
        ->build()
        ->key();
    $k2 = ModelCachelet::forModel($m2)
        ->withDates(['email_verified_at'])
        ->build()
        ->key();

    expect($k1)->not->toBe($k2);
});

it('withTimestamps() includes created_at and updated_at', function () {
    $d1 = Carbon::now()->subDays(1);
    $d2 = Carbon::now();

    $m1 = new Dummy(['id' => 5, 'name' => 'Eve', 'created_at' => $d1, 'updated_at' => $d1]);
    $m2 = new Dummy(['id' => 5, 'name' => 'Eve', 'created_at' => $d2, 'updated_at' => $d2]);

    $k1 = ModelCachelet::forModel($m1)
        ->withTimestamps()
        ->build()
        ->key();
    $k2 = ModelCachelet::forModel($m2)
        ->withTimestamps()
        ->build()
        ->key();

    expect($k1)->not->toBe($k2);
});
