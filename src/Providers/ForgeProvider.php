<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;
use Maarheeze\Compass\Collector\Forge\ForgeClient;
use Maarheeze\Compass\Collector\Forge\ServerNameFilter;
use Maarheeze\Compass\Collector\Forge\SitesQuery;
use Maarheeze\Compass\Console\SyncForgeCommand;
use Maarheeze\Compass\Http\ForgeSiteUrlFactory;

class ForgeProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->commands([SyncForgeCommand::class]);

        $this->callAfterResolving(Schedule::class, static function (Schedule $schedule): void {
            $schedule->command(SyncForgeCommand::class)
                ->hourly();
        });
    }

    public function register(): void
    {
        $this->app
            ->when(ForgeClient::class)
            ->needs('$token')
            ->giveConfig('compass.collector.forge_token');

        $this->app
            ->when([ForgeSiteUrlFactory::class, SitesQuery::class])
            ->needs('$organization')
            ->giveConfig('compass.forge_organization');

        $this->app
            ->when(ServerNameFilter::class)
            ->needs('$pattern')
            ->giveConfig('compass.forge_server_pattern');
    }
}
