<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Providers;

use Illuminate\Support\ServiceProvider;
use Maarheeze\Compass\Console\CheckSiteEndpointsCommand;

class SiteEndpointProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->commands([CheckSiteEndpointsCommand::class]);
    }
}
