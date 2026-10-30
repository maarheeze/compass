<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Providers;

use Illuminate\Config\Repository;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\ServiceProvider;
use Maarheeze\Compass\Analyzer\Forge\DeploymentStateClassifier;
use Maarheeze\Compass\Collector\Forge\ForgeClient;
use Maarheeze\Compass\Collector\Forge\ServerNameFilter;
use Maarheeze\Compass\Collector\Forge\SitesQuery;
use Maarheeze\Compass\Console\SyncForgeCommand;
use Maarheeze\Compass\Http\ForgeSiteUrlFactory;

class ForgeProvider extends ServiceProvider
{
    public function boot(Repository $config): void
    {
        $this->commands([SyncForgeCommand::class]);

        $this->callAfterResolving(Schedule::class, static function (Schedule $schedule) use ($config): void {
            $schedule->command(SyncForgeCommand::class)
                ->hourly()
                ->when(static function () use ($config): bool {
                    return $config->boolean('compass.schedule.forge');
                });
        });
    }

    public function register(): void
    {
        $this->app
            ->when(DeploymentStateClassifier::class)
            ->needs('$autoDeployGraceMinutes')
            ->give(static function (Container $container): int {
                return $container->make(Repository::class)->integer('compass.forge.auto_deploy_grace_minutes');
            });

        $this->app
            ->when(ForgeClient::class)
            ->needs('$token')
            ->giveConfig('compass.forge.token');

        $this->app
            ->when([ForgeSiteUrlFactory::class, SitesQuery::class])
            ->needs('$organization')
            ->giveConfig('compass.forge.organization');

        $this->app
            ->when(ServerNameFilter::class)
            ->needs('$pattern')
            ->giveConfig('compass.forge.server_pattern');
    }
}
