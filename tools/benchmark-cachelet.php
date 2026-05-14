<?php

declare(strict_types=1);

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Oxhq\Cachelet\Facades\Cachelet;
use Oxhq\Cachelet\Tests\TestCase;
use Tests\Models\Dummy;

require dirname(__DIR__).'/vendor/autoload.php';

$runner = new class('runTest') extends TestCase
{
    public function bootHarness(): void
    {
        $this->setUp();
    }

    public function shutdownHarness(): void
    {
        $this->app?->flush();
        $this->app = null;
    }

    public function appInstance()
    {
        return $this->app;
    }
};

$measure = static function (callable $callback, int $iterations = 1, ?callable $beforeEach = null): array {
    $durations = [];

    for ($index = 0; $index < $iterations; $index++) {
        if ($beforeEach !== null) {
            $beforeEach($index);
        }

        $start = hrtime(true);
        $callback($index);
        $durations[] = (hrtime(true) - $start) / 1_000_000;
    }

    return [
        'iterations' => $iterations,
        'min_ms' => min($durations),
        'max_ms' => max($durations),
        'avg_ms' => array_sum($durations) / count($durations),
    ];
};

$iterations = max(1, (int) ($_SERVER['CACHELET_BENCH_ITERATIONS'] ?? getenv('CACHELET_BENCH_ITERATIONS') ?: 10));
$store = (string) ($_SERVER['CACHELET_BENCH_STORE'] ?? getenv('CACHELET_BENCH_STORE') ?: 'array');
$outputPath = (string) ($_SERVER['CACHELET_BENCH_OUTPUT'] ?? getenv('CACHELET_BENCH_OUTPUT') ?: dirname(__DIR__).'/artifacts/benchmarks/cachelet-benchmark.json');

$runner->bootHarness();

try {
    $app = $runner->appInstance();

    config(['app.key' => 'base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=']);
    config(['cache.default' => $store]);

    if ($store === 'file') {
        config(['cache.stores.file' => [
            'driver' => 'file',
            'path' => storage_path('framework/cache/data'),
        ]]);
    }

    if ($store === 'redis') {
        config(['database.redis' => redisTestConfig()]);
        config(['cache.stores.redis' => [
            'driver' => 'redis',
            'connection' => 'cache',
            'lock_connection' => 'cache',
        ]]);
        app('cache')->forgetDriver('redis');
        Cache::store('redis')->flush();
    }

    app('cache')->forgetDriver($store);
    Cache::store($store)->flush();

    Dummy::query()->truncate();
    Dummy::query()->create(['name' => 'Ada', 'role' => 'admin']);
    Dummy::query()->create(['name' => 'Grace', 'role' => 'admin']);
    Dummy::query()->create(['name' => 'Linus', 'role' => 'user']);

    Route::get('/bench/users', function () {
        return response()->json(
            Dummy::query()
                ->where('role', request('role', 'admin'))
                ->cachelet()
                ->ttl(300)
                ->rememberQuery()
                ->pluck('name')
                ->all(),
        );
    })->name('bench.users')->cachelet(300, [
        'vary' => ['query' => true],
        'namespace' => 'bench.users',
    ]);

    $results = [];

    $coreBuilder = Cachelet::for('bench.core.hit')
        ->from(['role' => 'admin'])
        ->onStore($store)
        ->ttl(300);

    Cache::store($store)->flush();
    $results['core_remember_miss'] = $measure(
        fn (int $index) => Cachelet::for('bench.core.miss')
            ->from(['role' => 'admin', 'iteration' => $index])
            ->onStore($store)
            ->ttl(300)
            ->remember(fn () => ['admins' => 2]),
        $iterations,
    );
    $coreBuilder->remember(fn () => ['admins' => 2]);
    $results['core_remember_hit'] = $measure(fn () => $coreBuilder->remember(fn () => ['admins' => 2]), $iterations);

    $queryBuilder = Dummy::query()
        ->where('role', 'admin')
        ->cachelet()
        ->onStore($store)
        ->ttl(300);

    $queryBuilder->invalidatePrefix();
    $results['query_miss'] = $measure(
        fn (int $index) => Dummy::query()
            ->where('role', 'admin')
            ->where('id', '>=', $index + 1)
            ->cachelet()
            ->onStore($store)
            ->ttl(300)
            ->rememberQuery(),
        $iterations,
    );
    $queryBuilder->rememberQuery();
    $results['query_hit'] = $measure(fn () => $queryBuilder->rememberQuery(), $iterations);

    $kernel = $app->make(Kernel::class);

    Cachelet::for('request::bench.users')->from(['role' => 'admin'])->onStore($store)->invalidatePrefix();
    $results['request_miss'] = $measure(function (int $index) use ($kernel): void {
        $requestMiss = Request::create('/bench/users?role=admin&iteration='.$index, 'GET');
        $response = $kernel->handle($requestMiss);
        $kernel->terminate($requestMiss, $response);
    }, $iterations);

    $requestWarm = Request::create('/bench/users?role=admin', 'GET');
    $response = $kernel->handle($requestWarm);
    $kernel->terminate($requestWarm, $response);

    $results['request_hit'] = $measure(function () use ($kernel): void {
        $requestHit = Request::create('/bench/users?role=admin', 'GET');
        $response = $kernel->handle($requestHit);
        $kernel->terminate($requestHit, $response);
    }, $iterations);

    $results['prefix_invalidation'] = $measure(
        fn (int $index) => Cachelet::for('bench.invalidate')
            ->from(['page' => $index, 'slot' => 1])
            ->onStore($store)
            ->ttl(300)
            ->invalidatePrefix(),
        $iterations,
        function (int $index) use ($store): void {
            Cachelet::for('bench.invalidate')
                ->from(['page' => $index, 'slot' => 1])
                ->onStore($store)
                ->ttl(300)
                ->remember(fn () => 'one');
            Cachelet::for('bench.invalidate')
                ->from(['page' => $index, 'slot' => 2])
                ->onStore($store)
                ->ttl(300)
                ->remember(fn () => 'two');
        },
    );

    $payload = [
        'generated_at' => date(DATE_ATOM),
        'store' => $store,
        'php' => PHP_VERSION,
        'laravel' => $app->version(),
        'iterations' => $iterations,
        'results' => $results,
    ];

    $outputDirectory = dirname($outputPath);

    if (! is_dir($outputDirectory)) {
        mkdir($outputDirectory, 0777, true);
    }

    file_put_contents($outputPath, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL);

    fwrite(STDOUT, sprintf('Benchmark report written to %s%s', $outputPath, PHP_EOL));
} finally {
    Carbon::setTestNow();
    $runner->shutdownHarness();
}
