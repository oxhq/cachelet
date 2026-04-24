<?php

declare(strict_types=1);

namespace Oxhq\Cachelet\Exporter\Tests\Feature;

use Illuminate\Support\Facades\Http;
use Oxhq\Cachelet\Exporter\Contracts\ExporterClient;
use Oxhq\Cachelet\Exporter\Tests\Support\InMemoryExporterTransport;
use Oxhq\Cachelet\Exporter\Tests\TestCase;
use Oxhq\Cachelet\Facades\Cachelet;
use Oxhq\Cachelet\ValueObjects\CacheCoordinate;
use Oxhq\Cachelet\ValueObjects\CacheTelemetryRecord;

final class CacheletExporterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        InMemoryExporterTransport::reset();
        $this->app->singleton(InMemoryExporterTransport::class, static fn (): InMemoryExporterTransport => new InMemoryExporterTransport);
    }

    public function test_it_exports_telemetry_records_when_cachelet_emits_them(): void
    {
        config([
            'cachelet-exporter.enabled' => true,
            'cachelet-exporter.transport' => InMemoryExporterTransport::class,
            'cachelet-exporter.source.instance' => 'node-a',
        ]);

        $builder = Cachelet::for('users.index')->from(['page' => 1]);

        $builder->remember(fn (): array => ['id' => 1]);
        $builder->fetch();

        $payloads = InMemoryExporterTransport::$payloads;

        $this->assertCount(3, $payloads);
        $this->assertSame('cachelet.cloud.export.v1', $payloads[0]['contract']);
        $this->assertSame('miss', $payloads[0]['record']['event']);
        $this->assertSame('stored', $payloads[1]['record']['event']);
        $this->assertSame('core', $payloads[1]['record']['module']);
        $this->assertSame('node-a', $payloads[1]['source']['instance']);
        $this->assertSame('hit', $payloads[2]['record']['event']);
    }

    public function test_it_is_a_noop_when_disabled(): void
    {
        config([
            'cachelet-exporter.enabled' => false,
            'cachelet-exporter.transport' => InMemoryExporterTransport::class,
        ]);

        Cachelet::for('users.index')->from(['page' => 1])->remember(fn (): array => ['id' => 1]);

        $this->assertCount(0, InMemoryExporterTransport::$payloads);
    }

    public function test_http_transport_posts_canonical_export_payloads(): void
    {
        Http::fake();

        config([
            'cachelet-exporter.enabled' => true,
            'cachelet-exporter.transport' => 'http',
            'cachelet-exporter.client.endpoint' => 'https://cloud.example.test/ingest',
            'cachelet-exporter.client.token' => 'secret-token',
            'cachelet-exporter.source.instance' => 'worker-1',
        ]);

        $record = CacheTelemetryRecord::capture(
            'stored',
            CacheCoordinate::fromArray([
                'prefix' => 'users.index',
                'key' => 'cachelet:test-key',
                'ttl' => 300,
                'module' => 'core',
                'store' => 'array',
            ]),
            ['access_strategy' => 'standard']
        );

        $this->app->make(ExporterClient::class)->export($record);

        Http::assertSent(function ($request): bool {
            return $request->url() === 'https://cloud.example.test/ingest'
                && $request->hasHeader('Authorization', 'Bearer secret-token')
                && $request['contract'] === 'cachelet.cloud.export.v1'
                && $request['record']['contract'] === 'cachelet.telemetry.v1'
                && $request['record']['module'] === 'core'
                && $request['source']['instance'] === 'worker-1';
        });
    }
}
