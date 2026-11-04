<?php

declare(strict_types=1);

namespace Maarheeze\Compass;

use Carbon\FactoryImmutable;
use Illuminate\Config\Repository as ConfigurationRepository;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Livewire\LivewireManager;
use Maarheeze\Compass\Collector\CollectorLogger;
use Maarheeze\Compass\Collector\Listeners\RecordRunFailure;
use Maarheeze\Compass\Http\Controllers\RunController;
use Maarheeze\Compass\Http\Controllers\SiteEndpointCheckController;
use Maarheeze\Compass\Http\Middleware\Authenticate;
use Maarheeze\Compass\Models\Repository;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\Models\SiteEndpoint;
use Maarheeze\Compass\Models\SiteReport;
use Maarheeze\Compass\Providers\AlertProvider;
use Maarheeze\Compass\Providers\AuthenticationProvider;
use Maarheeze\Compass\Providers\ClientProvider;
use Maarheeze\Compass\Providers\CollectorProvider;
use Maarheeze\Compass\Providers\ScheduleProvider;
use Psr\Clock\ClockInterface;

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
            ->when([RunController::class, SiteEndpointCheckController::class])
            ->needs('$itemsPerPage')
            ->give(static function (Container $container): int {
                return $container->make(ConfigurationRepository::class)->integer('compass.items_per_page');
            });

        $this->app->register(AlertProvider::class);
        $this->app->register(AuthenticationProvider::class);
        $this->app->register(ClientProvider::class);
        $this->app->register(CollectorProvider::class);
        $this->app->register(ScheduleProvider::class);
    }

    public function boot(Dispatcher $events, LivewireManager $livewire): void
    {
        $events->listen(JobFailed::class, RecordRunFailure::class);

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

        $livewire->addNamespace('compass', classNamespace: 'Maarheeze\\Compass\\Livewire');
        $livewire->addPersistentMiddleware([Authenticate::class]);

        $this->publishes([
            __DIR__ . '/../config/compass.php' => config_path('compass.php'),
        ], 'compass-config');
    }
}
