<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;
use Maarheeze\Compass\Console\CheckSiteEndpointsCommand;

class SiteEndpointProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->commands([CheckSiteEndpointsCommand::class]);

        $this->callAfterResolving(Schedule::class, static function (Schedule $schedule): void {
            $schedule->command(CheckSiteEndpointsCommand::class)
                ->everyFifteenMinutes();
        });
    }
}
