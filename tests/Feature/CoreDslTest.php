<?php

declare(strict_types=1);

use Garaekz\Cachelet\Facades\Cachelet;
use Garaekz\Cachelet\Tests\TestCase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

uses(TestCase::class);

it('same payload in different order yields same key', function () {
    $p1 = ['b' => 2, 'a' => 1];
    $p2 = ['a' => 1, 'b' => 2];

    $c1 = Cachelet::for('test')->from($p1)->ttl(null);
    $c2 = Cachelet::for('test')->from($p2)->ttl(null);

    expect($c1->key())->toBe($c2->key());
});

it('default ttl when none provided', function () {
    $default = 3600; // 1 hour in seconds
    $c = Cachelet::for('demo')->from('x')->ttl(null);

    expect($c->duration())->toBe($default);
});

it('int ttl works and throws on invalid', function () {
    $c = Cachelet::for('int')->from('x')->ttl(120);
    expect($c->duration())->toBe(120);

    $this->expectException(\InvalidArgumentException::class);
    Cachelet::for('neg')->from('x')->ttl(0)->duration();
});

it('string ttl via Carbon parse', function () {
    $c = Cachelet::for('str')->from('y')->ttl('+2 hours');
    expect($c->duration())->toBe(7200);
});

it('Carbon ttl works', function () {
    $expires = Carbon::now()->addMinutes(5);
    $c = Cachelet::for('car')->from('y')->ttl($expires);
    expect($c->duration())->toBe(300);
});

it('expiresAt adds seconds to now', function () {
    $c = Cachelet::for('exp')->from('y')->ttl(30);
    expect($c->expiresAt()->timestamp)->toBe(Carbon::now()->addSeconds(30)->timestamp);
});

it('fetch without callback returns null if not stored', function () {
    Cache::flush();
    $val = Cachelet::for('f')->from('x')->ttl(10)->fetch();
    expect($val)->toBeNull();
});

it('fetch remembers and retrieves', function () {
    Cache::flush();
    $c = Cachelet::for('remember')->from('z')->ttl(10);

    $first = $c->fetch(fn () => 'computed');
    $second = $c->fetch();

    expect($first)->toBe('computed')
        ->and($second)->toBe('computed')
        ->and(Cache::has($c->key()))->toBeTrue();
});
