<?php

namespace Oxhq\Cachelet\Exporter;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Events\Dispatcher;
use Illuminate\Http\Client\Factory;
use Illuminate\Log\LogManager;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;
use Oxhq\Cachelet\Events\CacheletTelemetryRecorded;
use Oxhq\Cachelet\Exporter\Clients\TelemetryExporterClient;
use Oxhq\Cachelet\Exporter\Contracts\ExporterClient;
use Oxhq\Cachelet\Exporter\Contracts\ExporterTransport;
use Oxhq\Cachelet\Exporter\Exporting\TelemetryExporter;
use Oxhq\Cachelet\Exporter\Listeners\ExportTelemetryRecord;
use Oxhq\Cachelet\Exporter\Transports\HttpExporterTransport;
use Oxhq\Cachelet\Exporter\Transports\LogExporterTransport;
use Oxhq\Cachelet\Exporter\Transports\NullExporterTransport;

class CacheletExporterServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/cachelet-exporter.php', 'cachelet-exporter');

        $this->app->singleton(NullExporterTransport::class, static fn (): NullExporterTransport => new NullExporterTransport);

        $this->app->singleton(LogExporterTransport::class, function (Application $app): LogExporterTransport {
            return new LogExporterTransport(
                $app->make(LogManager::class),
                $app['config']->get('cachelet-exporter.log.channel')
            );
        });

        $this->app->singleton(HttpExporterTransport::class, function (Application $app): HttpExporterTransport {
            $endpoint = (string) $app['config']->get('cachelet-exporter.client.endpoint', '');

            if ($endpoint === '') {
                throw new InvalidArgumentException('cachelet-exporter.client.endpoint must be configured for the http transport.');
            }

            return new HttpExporterTransport(
                http: $app->make(Factory::class),
                endpoint: $endpoint,
                token: $app['config']->get('cachelet-exporter.client.token'),
                timeout: (int) $app['config']->get('cachelet-exporter.client.timeout', 3),
                headers: (array) $app['config']->get('cachelet-exporter.client.headers', []),
            );
        });

        $this->app->singleton(ExporterTransport::class, fn (Application $app): ExporterTransport => $this->resolveTransport($app));

        $this->app->singleton(ExporterClient::class, function (Application $app): ExporterClient {
            return new TelemetryExporterClient(
                transport: $app->make(ExporterTransport::class),
                source: (array) $app['config']->get('cachelet-exporter.source', []),
            );
        });

        $this->app->singleton(TelemetryExporter::class, function (Application $app): TelemetryExporter {
            return new TelemetryExporter(
                client: $app->make(ExporterClient::class),
                logs: $app->make(LogManager::class),
                enabled: (bool) $app['config']->get('cachelet-exporter.enabled', false),
                throwFailures: (bool) $app['config']->get('cachelet-exporter.throw', false),
            );
        });
    }

    public function boot(Dispatcher $events): void
    {
        $this->publishes([
            __DIR__.'/../config/cachelet-exporter.php' => config_path('cachelet-exporter.php'),
        ], 'cachelet-exporter-config');

        $events->listen(CacheletTelemetryRecorded::class, ExportTelemetryRecord::class);
    }

    protected function resolveTransport(Application $app): ExporterTransport
    {
        $transport = $app['config']->get('cachelet-exporter.transport', 'null');

        if (is_string($transport) && class_exists($transport) && is_a($transport, ExporterTransport::class, true)) {
            return $app->make($transport);
        }

        return match ($transport) {
            'null' => $app->make(NullExporterTransport::class),
            'log' => $app->make(LogExporterTransport::class),
            'http' => $app->make(HttpExporterTransport::class),
            default => throw new InvalidArgumentException(sprintf(
                'Unsupported cachelet-exporter transport [%s].',
                is_scalar($transport) ? (string) $transport : get_debug_type($transport)
            )),
        };
    }
}
