<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Providers;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\ServiceProvider;
use Maarheeze\Compass\Alerts\Events\ConditionObserved;
use Maarheeze\Compass\Alerts\Listeners\EvaluateDebugEnabled;
use Maarheeze\Compass\Alerts\Listeners\EvaluateDownSiteEndpoint;
use Maarheeze\Compass\Alerts\Listeners\ObservationHandler;
use Maarheeze\Compass\Collector\Events\SiteEndpointChecked;
use Maarheeze\Compass\Collector\Events\SiteInspected;

class AlertProvider extends ServiceProvider
{
    public function boot(Dispatcher $events): void
    {
        $events->listen(ConditionObserved::class, ObservationHandler::class);
        $events->listen(SiteEndpointChecked::class, EvaluateDownSiteEndpoint::class);
        $events->listen(SiteInspected::class, EvaluateDebugEnabled::class);
    }
}
