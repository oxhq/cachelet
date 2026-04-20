<?php

namespace Oxhq\Cachelet\Cloud;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Events\Dispatcher;
use Illuminate\Http\Client\Factory;
use Illuminate\Log\LogManager;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;
use Oxhq\Cachelet\Cloud\Clients\TelemetryCloudClient;
use Oxhq\Cachelet\Cloud\Contracts\CloudClient;
use Oxhq\Cachelet\Cloud\Contracts\CloudTransport;
use Oxhq\Cachelet\Cloud\Exporting\TelemetryExporter;
use Oxhq\Cachelet\Cloud\Listeners\ExportTelemetryRecord;
use Oxhq\Cachelet\Cloud\Transports\HttpCloudTransport;
use Oxhq\Cachelet\Cloud\Transports\LogCloudTransport;
use Oxhq\Cachelet\Cloud\Transports\NullCloudTransport;
use Oxhq\Cachelet\Events\CacheletTelemetryRecorded;

class CacheletCloudServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/cachelet-cloud.php', 'cachelet-cloud');

        $this->app->singleton(NullCloudTransport::class, static fn (): NullCloudTransport => new NullCloudTransport);

        $this->app->singleton(LogCloudTransport::class, function (Application $app): LogCloudTransport {
            return new LogCloudTransport(
                $app->make(LogManager::class),
                $app['config']->get('cachelet-cloud.log.channel')
            );
        });

        $this->app->singleton(HttpCloudTransport::class, function (Application $app): HttpCloudTransport {
            $endpoint = (string) $app['config']->get('cachelet-cloud.client.endpoint', '');

            if ($endpoint === '') {
                throw new InvalidArgumentException('cachelet-cloud.client.endpoint must be configured for the http transport.');
            }

            return new HttpCloudTransport(
                http: $app->make(Factory::class),
                endpoint: $endpoint,
                token: $app['config']->get('cachelet-cloud.client.token'),
                timeout: (int) $app['config']->get('cachelet-cloud.client.timeout', 3),
                headers: (array) $app['config']->get('cachelet-cloud.client.headers', []),
            );
        });

        $this->app->singleton(CloudTransport::class, fn (Application $app): CloudTransport => $this->resolveTransport($app));

        $this->app->singleton(CloudClient::class, function (Application $app): CloudClient {
            return new TelemetryCloudClient(
                transport: $app->make(CloudTransport::class),
                source: (array) $app['config']->get('cachelet-cloud.source', []),
            );
        });

        $this->app->singleton(TelemetryExporter::class, function (Application $app): TelemetryExporter {
            return new TelemetryExporter(
                client: $app->make(CloudClient::class),
                logs: $app->make(LogManager::class),
                enabled: (bool) $app['config']->get('cachelet-cloud.enabled', false),
                throwFailures: (bool) $app['config']->get('cachelet-cloud.throw', false),
            );
        });
    }

    public function boot(Dispatcher $events): void
    {
        $this->publishes([
            __DIR__.'/../config/cachelet-cloud.php' => config_path('cachelet-cloud.php'),
        ], 'cachelet-cloud-config');

        $events->listen(CacheletTelemetryRecorded::class, ExportTelemetryRecord::class);
    }

    protected function resolveTransport(Application $app): CloudTransport
    {
        $transport = $app['config']->get('cachelet-cloud.transport', 'null');

        if (is_string($transport) && class_exists($transport) && is_a($transport, CloudTransport::class, true)) {
            return $app->make($transport);
        }

        return match ($transport) {
            'null' => $app->make(NullCloudTransport::class),
            'log' => $app->make(LogCloudTransport::class),
            'http' => $app->make(HttpCloudTransport::class),
            default => throw new InvalidArgumentException(sprintf(
                'Unsupported cachelet-cloud transport [%s].',
                is_scalar($transport) ? (string) $transport : get_debug_type($transport)
            )),
        };
    }
}
