<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Oxhq\Cachelet\Request\Support\CacheletRequestManager;
use Oxhq\Cachelet\Request\Support\ResponseCacheProfile;
use Oxhq\Cachelet\ValueObjects\CacheScope;
use Tests\Models\TestUser;

beforeEach(function () {
    Cache::flush();
});

it('stamps request coordinates with a canonical module discriminator', function () {
    $profile = app(CacheletRequestManager::class)->for(request(), 'users');
    $coordinate = $profile->builder()->coordinate();

    expect($profile)->toBeInstanceOf(ResponseCacheProfile::class)
        ->and($coordinate->module)->toBe('request')
        ->and($coordinate->metadata['type'])->toBe('request');
});

it('prefers an explicit scope over the inferred request scope', function () {
    $profile = app(CacheletRequestManager::class)->for(request(), 'users');
    $projection = $profile->builder()
        ->scope(CacheScope::named('edge.users'))
        ->coordinate()
        ->toProjection();

    expect($projection['scope'])->toMatchArray([
        'contract' => 'cachelet.scope.v1',
        'identifier' => 'edge.users',
        'source' => 'explicit',
    ]);
});

it('infers a stable scope from the request namespace grouping', function () {
    $first = app(CacheletRequestManager::class)
        ->for(Request::create('/users', 'GET'), null, ['namespace' => 'users'])
        ->builder()
        ->coordinate()
        ->toProjection();

    $second = app(CacheletRequestManager::class)
        ->for(Request::create('/users?role=admin', 'GET'), null, ['namespace' => 'users'])
        ->builder()
        ->coordinate()
        ->toProjection();

    expect($first['scope']['source'])->toBe('inferred')
        ->and($second['scope']['source'])->toBe('inferred')
        ->and($first['scope']['identifier'])->not->toBe('')
        ->and($first['scope']['identifier'])->toBe($second['scope']['identifier']);
});

it('caches route responses and varies by query parameters', function () {
    $calls = 0;

    Route::get('/cachelet-query-users', function () use (&$calls) {
        return response()->json(['call' => ++$calls]);
    })->name('users.query')->cachelet();

    $first = $this->get('/cachelet-query-users?role=admin');
    $second = $this->get('/cachelet-query-users?role=admin');
    $third = $this->get('/cachelet-query-users?role=staff');

    $first->assertOk()->assertJson(['call' => 1]);
    $second->assertOk()->assertJson(['call' => 1]);
    $third->assertOk()->assertJson(['call' => 2]);
});

it('can vary request cache entries by selected headers and authenticated user', function () {
    $calls = 0;
    $firstUser = TestUser::query()->create(['name' => 'Jane', 'email' => 'jane@example.com']);
    $secondUser = TestUser::query()->create(['name' => 'John', 'email' => 'john@example.com']);

    Route::get('/cachelet-auth-users', function () use (&$calls) {
        return response((string) ++$calls);
    })->name('users.auth')->cachelet(null, [
        'vary' => [
            'headers' => ['X-Tenant'],
            'auth' => true,
        ],
    ]);

    $this->actingAs($firstUser)->get('/cachelet-auth-users', ['X-Tenant' => 'a'])->assertSeeText('1');
    $this->actingAs($firstUser)->get('/cachelet-auth-users', ['X-Tenant' => 'a'])->assertSeeText('1');
    $this->actingAs($firstUser)->get('/cachelet-auth-users', ['X-Tenant' => 'b'])->assertSeeText('2');
    $this->actingAs($secondUser)->get('/cachelet-auth-users', ['X-Tenant' => 'b'])->assertSeeText('3');
});

it('invalidates route groups by namespace prefix', function () {
    $calls = 0;

    Route::get('/cachelet-group-users', function () use (&$calls) {
        return response((string) ++$calls);
    })->name('users.group')->cachelet(null, ['namespace' => 'users']);

    $this->get('/cachelet-group-users')->assertSeeText('1');
    $this->get('/cachelet-group-users')->assertSeeText('1');

    $deleted = app(CacheletRequestManager::class)->invalidateNamespace('users');

    $this->get('/cachelet-group-users')->assertSeeText('2');

    expect($deleted)->not->toBeEmpty();
});

it('bypasses streamed responses instead of throwing or caching them', function () {
    $calls = 0;

    Route::get('/cachelet-stream-users', function () use (&$calls) {
        $call = ++$calls;

        return response()->stream(function () use ($call): void {
            echo "stream-{$call}";
        }, 200, ['X-Call' => (string) $call]);
    })->name('users.stream')->cachelet();

    $first = $this->get('/cachelet-stream-users');
    $second = $this->get('/cachelet-stream-users');

    $first->assertOk();
    $second->assertOk();

    expect($first->streamedContent())->toBe('stream-1')
        ->and($second->streamedContent())->toBe('stream-2')
        ->and($first->headers->get('X-Call'))->toBe('1')
        ->and($second->headers->get('X-Call'))->toBe('2');
});

it('bypasses binary download responses instead of throwing or caching them', function () {
    $calls = 0;

    Route::get('/cachelet-download-users', function () use (&$calls) {
        $call = ++$calls;
        $path = tempnam(sys_get_temp_dir(), 'cachelet-request-');
        file_put_contents($path, "download-{$call}");

        return response()
            ->download($path, "users-{$call}.txt", ['X-Call' => (string) $call])
            ->deleteFileAfterSend(true);
    })->name('users.download')->cachelet();

    $first = $this->get('/cachelet-download-users');
    $second = $this->get('/cachelet-download-users');

    $first->assertOk();
    $second->assertOk();

    expect($first->getFile()->getContent())->toBe('download-1')
        ->and($second->getFile()->getContent())->toBe('download-2')
        ->and($first->headers->get('X-Call'))->toBe('1')
        ->and($second->headers->get('X-Call'))->toBe('2');
});

it('bypasses non-cacheable statuses instead of throwing or caching them', function () {
    $calls = 0;

    Route::get('/cachelet-created-users', function () use (&$calls) {
        return response()->json(['call' => ++$calls], 201, ['X-Call' => (string) $calls]);
    })->name('users.created')->cachelet();

    $first = $this->get('/cachelet-created-users');
    $second = $this->get('/cachelet-created-users');

    $first->assertCreated()->assertJson(['call' => 1]);
    $second->assertCreated()->assertJson(['call' => 2]);

    expect($first->headers->get('X-Call'))->toBe('1')
        ->and($second->headers->get('X-Call'))->toBe('2');
});

it('keeps the last cacheable response when an swr refresh returns a non-cacheable response', function () {
    $calls = 0;

    Route::get('/cachelet-stale-users', function () use (&$calls) {
        $call = ++$calls;

        if ($call === 1) {
            return response("cached-{$call}", 200, ['X-Call' => (string) $call]);
        }

        return response()->stream(function () use ($call): void {
            echo "stream-{$call}";
        }, 200, ['X-Call' => (string) $call]);
    })->name('users.stale')->cachelet(1, ['stale' => true]);

    $initial = $this->get('/cachelet-stale-users');
    $initial->assertOk()->assertSeeText('cached-1');

    try {
        Carbon::setTestNow(now()->addSeconds(2));

        $stale = $this->get('/cachelet-stale-users');
        $afterRefresh = $this->get('/cachelet-stale-users');
    } finally {
        Carbon::setTestNow();
    }

    $stale->assertOk()->assertSeeText('cached-1');
    $afterRefresh->assertOk()->assertSeeText('cached-1');

    expect($stale->headers->get('X-Call'))->toBe('1')
        ->and($afterRefresh->headers->get('X-Call'))->toBe('1');
});
