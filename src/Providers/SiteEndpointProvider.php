<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Providers;

use Illuminate\Config\Repository;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;
use Maarheeze\Compass\Console\CheckSiteEndpointsCommand;

class SiteEndpointProvider extends ServiceProvider
{
    public function boot(Repository $config): void
    {
        $this->commands([CheckSiteEndpointsCommand::class]);

        $this->callAfterResolving(Schedule::class, static function (Schedule $schedule) use ($config): void {
            $schedule->command(CheckSiteEndpointsCommand::class)
                ->everyFifteenMinutes()
                ->when(static function () use ($config): bool {
                    return $config->boolean('compass.schedule.site_endpoints');
                });
        });
    }
}
