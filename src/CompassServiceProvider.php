<?php

declare(strict_types=1);

namespace Maarheeze\Compass;

use Carbon\FactoryImmutable;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Maarheeze\Compass\Collector\CollectorLogger;
use Maarheeze\Compass\Providers\AlertProvider;
use Maarheeze\Compass\Providers\AuthenticationProvider;
use Maarheeze\Compass\Providers\ClientProvider;
use Maarheeze\Compass\Providers\ForgeProvider;
use Maarheeze\Compass\Providers\GitHubProvider;
use Maarheeze\Compass\Providers\SiteEndpointProvider;
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

        $this->app->register(AlertProvider::class);
        $this->app->register(AuthenticationProvider::class);
        $this->app->register(ClientProvider::class);
        $this->app->register(ForgeProvider::class);
        $this->app->register(GitHubProvider::class);
        $this->app->register(SiteEndpointProvider::class);
    }

    public function boot(): void
    {
        $this->loadTranslationsFrom(sprintf('%s/../lang', __DIR__), 'compass');
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'compass');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        Blade::anonymousComponentPath(__DIR__ . '/../resources/views/components', 'compass');
        Blade::componentNamespace('Maarheeze\\Compass\\View\\Components', 'compass');

        $this->publishes([
            __DIR__ . '/../config/compass.php' => config_path('compass.php'),
        ], 'compass-config');
    }
}
