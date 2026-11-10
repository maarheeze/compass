<?php

declare(strict_types=1);

namespace Maarheeze\Compass;

use Carbon\FactoryImmutable;
use Illuminate\Broadcasting\Broadcasters\Broadcaster;
use Illuminate\Broadcasting\BroadcastManager;
use Illuminate\Config\Repository as ConfigurationRepository;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Maarheeze\Compass\Alerts\Listeners\BroadcastAlertHistoryEntry;
use Maarheeze\Compass\Broadcasting\CompassChannel;
use Maarheeze\Compass\Collector\CollectorLogger;
use Maarheeze\Compass\Collector\Events\RunSaved;
use Maarheeze\Compass\Collector\Listeners\BroadcastRunStatus;
use Maarheeze\Compass\Collector\Listeners\RecordRunFailure;
use Maarheeze\Compass\Collector\SiteEndpoint\EndpointPath;
use Maarheeze\Compass\Http\Controllers\RunController;
use Maarheeze\Compass\Http\Controllers\SiteEndpointCheckController;
use Maarheeze\Compass\Http\EndpointPathRouteParameter;
use Maarheeze\Compass\Models\Repository;
use Maarheeze\Compass\Models\Run;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\Models\SiteEndpoint;
use Maarheeze\Compass\Models\SiteReport;
use Maarheeze\Compass\Providers\AlertProvider;
use Maarheeze\Compass\Providers\AuthenticationProvider;
use Maarheeze\Compass\Providers\CollectorProvider;
use Maarheeze\Compass\Providers\ScheduleProvider;
use Psr\Clock\ClockInterface;
use Webmozart\Assert\Assert;

use function config_path;
use function sprintf;

class CompassServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/compass.php', 'compass');

        $this->app->bindIf(ClockInterface::class, FactoryImmutable::class);

        $this->app
            ->when(CollectorLogger::class)
            ->needs('$channel')
            ->giveConfig('compass.log_channel');

        $this->app
            ->when([BroadcastAlertHistoryEntry::class, BroadcastRunStatus::class])
            ->needs('$broadcastQueue')
            ->giveConfig('compass.broadcast_queue');

        $this->app
            ->when([RunController::class, SiteEndpointCheckController::class])
            ->needs('$itemsPerPage')
            ->give(static function (Container $container): int {
                return $container->make(ConfigurationRepository::class)->integer('compass.items_per_page');
            });

        $this->app->register(AlertProvider::class);
        $this->app->register(AuthenticationProvider::class);
        $this->app->register(CollectorProvider::class);
        $this->app->register(ScheduleProvider::class);
    }

    public function boot(
        BroadcastManager $broadcastManager,
        Dispatcher $events,
        EndpointPathRouteParameter $endpointPathRouteParameter,
        Router $router,
    ): void {
        $events->listen(JobFailed::class, RecordRunFailure::class);
        $events->listen(RunSaved::class, BroadcastRunStatus::class);

        Run::saved(static function (Run $run) use ($events): void {
            $events->dispatch(new RunSaved($run));
        });

        Assert::isInstanceOf($broadcastManager->connection(), Broadcaster::class)->channel(
            CompassChannel::NAME,
            CompassChannel::class,
            ['guards' => [AuthenticationProvider::GUARD]],
        );

        $router->bind('endpointPath', static function (string $routeParameter) use ($endpointPathRouteParameter): EndpointPath {
            return $endpointPathRouteParameter->decode($routeParameter);
        });

        $this->loadTranslationsFrom(sprintf('%s/../lang', __DIR__), 'compass');
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'compass');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        Relation::morphMap([
            'repository' => Repository::class,
            'site' => Site::class,
            'site-endpoint' => SiteEndpoint::class,
            'site-report' => SiteReport::class,
        ]);

        Blade::anonymousComponentPath(__DIR__ . '/../resources/views/components', 'compass');
        Blade::componentNamespace('Maarheeze\\Compass\\View\\Components', 'compass');

        $this->publishes([
            __DIR__ . '/../config/compass.php' => config_path('compass.php'),
        ], 'compass-config');
    }
}
