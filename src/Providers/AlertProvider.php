<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Providers;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\ServiceProvider;
use Maarheeze\Compass\Alerts\Events\ConditionObserved;
use Maarheeze\Compass\Alerts\Listeners\Client\EvaluateDebugEnabled;
use Maarheeze\Compass\Alerts\Listeners\Client\EvaluateIncompatibleClient;
use Maarheeze\Compass\Alerts\Listeners\Client\EvaluateNonProduction;
use Maarheeze\Compass\Alerts\Listeners\Client\EvaluateUnauthorizedClient;
use Maarheeze\Compass\Alerts\Listeners\Composer\EvaluateComposerWarning;
use Maarheeze\Compass\Alerts\Listeners\Forge\EvaluateMissingSite;
use Maarheeze\Compass\Alerts\Listeners\Forge\EvaluateOverdueDeployment;
use Maarheeze\Compass\Alerts\Listeners\Forge\EvaluateSiteBehind;
use Maarheeze\Compass\Alerts\Listeners\GitHub\EvaluateMissingRepository;
use Maarheeze\Compass\Alerts\Listeners\ObservationHandler;
use Maarheeze\Compass\Alerts\Listeners\SiteEndpoint\EvaluateDownSiteEndpoint;
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
        $events->listen(GitHubRunSucceeded::class, EvaluateMissingRepository::class);
        $events->listen(RepositoryMissingIgnored::class, EvaluateMissingRepository::class);
        $events->listen(GitHubRunSucceeded::class, EvaluateComposerWarning::class);
        $events->listen(ForgeRunSucceeded::class, EvaluateMissingSite::class);
        $events->listen(SiteMissingIgnored::class, EvaluateMissingSite::class);
        $events->listen(GitHubRunSucceeded::class, EvaluateSiteBehind::class);
        $events->listen(ForgeRunSucceeded::class, EvaluateSiteBehind::class);
        $events->listen(ForgeRunSucceeded::class, EvaluateOverdueDeployment::class);
        $events->listen(ForgeRunSucceeded::class, EvaluateDownSiteEndpoint::class);
        $events->listen(ForgeRunSucceeded::class, EvaluateDebugEnabled::class);
        $events->listen(ForgeRunSucceeded::class, EvaluateNonProduction::class);
        $events->listen(ForgeRunSucceeded::class, EvaluateUnauthorizedClient::class);
        $events->listen(ForgeRunSucceeded::class, EvaluateIncompatibleClient::class);
    }
}
