<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;
use Maarheeze\Compass\Collector\SiteEndpoint\Jobs\CheckSiteEndpoints;

class SiteEndpointProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->callAfterResolving(Schedule::class, static function (Schedule $schedule): void {
            $schedule->job(CheckSiteEndpoints::class)
                ->everyFifteenMinutes();
        });
    }
}
