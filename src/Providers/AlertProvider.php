<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Providers;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\ServiceProvider;
use Maarheeze\Compass\Alerts\Events\ConditionObserved;
use Maarheeze\Compass\Alerts\Listeners\EvaluateComposerWarnings;
use Maarheeze\Compass\Alerts\Listeners\EvaluateDebugEnabled;
use Maarheeze\Compass\Alerts\Listeners\EvaluateDownSiteEndpoint;
use Maarheeze\Compass\Alerts\Listeners\EvaluateIncompatibleClient;
use Maarheeze\Compass\Alerts\Listeners\EvaluateMissingRepositories;
use Maarheeze\Compass\Alerts\Listeners\EvaluateMissingSites;
use Maarheeze\Compass\Alerts\Listeners\EvaluateNonProduction;
use Maarheeze\Compass\Alerts\Listeners\EvaluateOverdueDeployments;
use Maarheeze\Compass\Alerts\Listeners\EvaluateSitesBehind;
use Maarheeze\Compass\Alerts\Listeners\EvaluateUnauthorizedClient;
use Maarheeze\Compass\Alerts\Listeners\ObservationHandler;
use Maarheeze\Compass\Collector\Events\ForgeRunSucceeded;
use Maarheeze\Compass\Collector\Events\GitHubRunSucceeded;
use Maarheeze\Compass\Collector\Events\RepositoryMissingIgnored;
use Maarheeze\Compass\Collector\Events\SiteEndpointChecked;
use Maarheeze\Compass\Collector\Events\SiteInspected;
use Maarheeze\Compass\Collector\Events\SiteMissingIgnored;

class AlertProvider extends ServiceProvider
{
    public function boot(Dispatcher $events): void
    {
        $events->listen(ConditionObserved::class, ObservationHandler::class);
        $events->listen(SiteEndpointChecked::class, EvaluateDownSiteEndpoint::class);
        $events->listen(SiteInspected::class, EvaluateDebugEnabled::class);
        $events->listen(SiteInspected::class, EvaluateNonProduction::class);
        $events->listen(SiteInspected::class, EvaluateUnauthorizedClient::class);
        $events->listen(SiteInspected::class, EvaluateIncompatibleClient::class);
        $events->listen(GitHubRunSucceeded::class, EvaluateMissingRepositories::class);
        $events->listen(RepositoryMissingIgnored::class, EvaluateMissingRepositories::class);
        $events->listen(GitHubRunSucceeded::class, EvaluateComposerWarnings::class);
        $events->listen(ForgeRunSucceeded::class, EvaluateMissingSites::class);
        $events->listen(SiteMissingIgnored::class, EvaluateMissingSites::class);
        $events->listen(GitHubRunSucceeded::class, EvaluateSitesBehind::class);
        $events->listen(ForgeRunSucceeded::class, EvaluateSitesBehind::class);
        $events->listen(ForgeRunSucceeded::class, EvaluateOverdueDeployments::class);
    }
}
