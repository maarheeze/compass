<?php

declare(strict_types=1);

namespace Maarheeze\Compass;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Maarheeze\Compass\Providers\AuthenticationProvider;

use function config_path;

class CompassServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/compass.php', 'compass');

        $this->app->register(AuthenticationProvider::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'compass');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        Blade::anonymousComponentPath(__DIR__ . '/../resources/views/components', 'compass');

        if (!$this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__ . '/../config/compass.php' => config_path('compass.php'),
        ], 'compass-config');
    }
}
