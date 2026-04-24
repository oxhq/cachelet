<?php

declare(strict_types=1);

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Oxhq\Cachelet\Facades\Cachelet;
use Oxhq\Cachelet\ValueObjects\CacheScope;
use Oxhq\Cachelet\ValueObjects\InterventionPreview;
use Oxhq\Cachelet\ValueObjects\InterventionReceipt;
use Oxhq\Cachelet\ValueObjects\VerificationResult;

beforeEach(function () {
    Cache::flush();
});

it('prefers an explicit scope over the inferred scope in coordinate projections', function () {
    $projection = Cachelet::for('users.index')
        ->scope(CacheScope::named('agency.users'))
        ->from(['page' => 1])
        ->coordinate()
        ->toProjection();

    expect($projection)->toMatchArray([
        'scope' => [
            'contract' => 'cachelet.scope.v1',
            'identifier' => 'agency.users',
            'source' => 'explicit',
        ],
    ]);
});

it('returns structured preview receipt and verification contracts for scope interventions', function () {
    Cachelet::for('users.index')
        ->scope(CacheScope::named('agency.users'))
        ->from(['page' => 1])
        ->remember(fn (): array => ['id' => 1]);

    $intervention = Cachelet::interventions()->forScope(CacheScope::named('agency.users'));

    $preview = $intervention->preview();
    $previewProjection = $preview->toArray();

    expect($preview)->toBeInstanceOf(InterventionPreview::class)
        ->and($previewProjection)->toMatchArray([
            'contract' => 'cachelet.intervention.preview.v1',
            'strategy' => 'scope',
            'observed_state_only' => true,
            'scope' => [
                'contract' => 'cachelet.scope.v1',
                'identifier' => 'agency.users',
                'source' => 'explicit',
            ],
        ])
        ->and($previewProjection['matched_coordinate_count'])->toBeGreaterThanOrEqual(1)
        ->and($previewProjection['coordinate_summaries'])->not->toBeEmpty()
        ->and($previewProjection['store_summaries'])->not->toBeEmpty()
        ->and($previewProjection['blast_radius'])->toBeIn(['low', 'medium', 'high'])
        ->and($previewProjection['caveats'])->toContain('Observed state only.');

    $receipt = $intervention->execute();
    $receiptProjection = $receipt->toArray();

    expect($receipt)->toBeInstanceOf(InterventionReceipt::class)
        ->and($receiptProjection)->toMatchArray([
            'contract' => 'cachelet.intervention.receipt.v1',
            'strategy' => 'scope',
            'scope' => [
                'contract' => 'cachelet.scope.v1',
                'identifier' => 'agency.users',
                'source' => 'explicit',
            ],
        ])
        ->and($receiptProjection['status'])->toBe('executed')
        ->and($receiptProjection['matched_coordinate_count'])->toBeGreaterThanOrEqual(1)
        ->and($receiptProjection['executed_at'])->not->toBeEmpty();

    $verification = $intervention->verify($receipt);
    $verificationProjection = $verification->toArray();

    expect($verification)->toBeInstanceOf(VerificationResult::class)
        ->and($verificationProjection)->toMatchArray([
            'contract' => 'cachelet.intervention.verification.v1',
            'scope' => [
                'contract' => 'cachelet.scope.v1',
                'identifier' => 'agency.users',
                'source' => 'explicit',
            ],
        ])
        ->and($verificationProjection['status'])->toBeIn(['recovering', 'verified', 'inconclusive', 'failed'])
        ->and($verificationProjection['observed_state_only'])->toBeTrue()
        ->and(array_key_exists('fresh_evidence', $verificationProjection))->toBeTrue()
        ->and(array_key_exists('stale_evidence', $verificationProjection))->toBeTrue()
        ->and(array_key_exists('caveats', $verificationProjection))->toBeTrue();
});

it('keeps legacy invalidation helpers compatible with scoped interventions', function () {
    $builder = Cachelet::for('users.index')
        ->scope(CacheScope::named('agency.users'))
        ->from(['page' => 1]);

    $builder->remember(fn (): array => ['id' => 1]);
    expect(Cache::has($builder->key()))->toBeTrue();

    $builder->invalidate();
    expect(Cache::has($builder->key()))->toBeFalse();

    $builder->remember(fn (): array => ['id' => 2]);
    $deleted = $builder->invalidatePrefix();

    expect($deleted)->toContain($builder->key())
        ->and(Cachelet::interventions()->forScope(CacheScope::named('agency.users'))->preview()->toArray()['matched_coordinate_count'])->toBe(0);
});

it('executes scoped interventions against the coordinate store instead of the default store', function () {
    config(['cache.default' => 'array']);
    $builder = Cachelet::for('users.index')
        ->onStore('file')
        ->scope(CacheScope::named('agency.users'))
        ->from(['page' => 1]);

    $builder->remember(fn (): array => ['id' => 1]);

    expect(Cache::store('file')->has($builder->key()))->toBeTrue();

    Cachelet::interventions()->forScope(CacheScope::named('agency.users'))->execute();

    expect(Cache::store('file')->has($builder->key()))->toBeFalse();
});

it('keeps scope interventions available while an swr entry is inside the grace window', function () {
    $builder = Cachelet::for('users.index')
        ->scope(CacheScope::named('agency.users'))
        ->from(['page' => 1])
        ->ttl(60);

    $builder->staleWhileRevalidate(fn (): array => ['id' => 1]);

    Carbon::setTestNow(Carbon::now()->addSeconds(61));

    $preview = Cachelet::interventions()
        ->forScope(CacheScope::named('agency.users'))
        ->preview()
        ->toArray();

    expect($preview['matched_coordinate_count'])->toBeGreaterThanOrEqual(1);
});
