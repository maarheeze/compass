<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Providers;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\ServiceProvider;
use Maarheeze\Compass\Alerts\Events\AlertOpened;
use Maarheeze\Compass\Alerts\Listeners\Client\EvaluateSiteClientIncompatible;
use Maarheeze\Compass\Alerts\Listeners\Client\EvaluateSiteClientUnauthorized;
use Maarheeze\Compass\Alerts\Listeners\Client\EvaluateSiteDebugEnabled;
use Maarheeze\Compass\Alerts\Listeners\Client\EvaluateSiteNotInProduction;
use Maarheeze\Compass\Alerts\Listeners\Composer\EvaluateComposerMinimumStability;
use Maarheeze\Compass\Alerts\Listeners\Composer\EvaluateComposerWarning;
use Maarheeze\Compass\Alerts\Listeners\Forge\EvaluateSiteBehind;
use Maarheeze\Compass\Alerts\Listeners\Forge\EvaluateSiteDeploymentOverdue;
use Maarheeze\Compass\Alerts\Listeners\Forge\EvaluateSiteMissing;
use Maarheeze\Compass\Alerts\Listeners\Forge\ResolveMissingSiteConditions;
use Maarheeze\Compass\Alerts\Listeners\GitHub\EvaluateDependabotCoverage;
use Maarheeze\Compass\Alerts\Listeners\GitHub\EvaluateRepositoryMissing;
use Maarheeze\Compass\Alerts\Listeners\SendAlertNotifications;
use Maarheeze\Compass\Alerts\Listeners\SiteEndpoint\EvaluateSiteEndpointDown;
use Maarheeze\Compass\Collector\Events\ComposerJsonChanged;
use Maarheeze\Compass\Collector\Events\ComposerLockChanged;
use Maarheeze\Compass\Collector\Events\DependabotYmlChanged;
use Maarheeze\Compass\Collector\Events\PackageJsonChanged;
use Maarheeze\Compass\Collector\Events\RepositoriesCollected;
use Maarheeze\Compass\Collector\Events\RepositoryMissingIgnored;
use Maarheeze\Compass\Collector\Events\SiteEndpointChecked;
use Maarheeze\Compass\Collector\Events\SiteInspected;
use Maarheeze\Compass\Collector\Events\SiteMissingIgnored;
use Maarheeze\Compass\Collector\Events\SitesCollected;

class AlertProvider extends ServiceProvider
{
    public function boot(Dispatcher $events): void
    {
        $events->listen(AlertOpened::class, SendAlertNotifications::class);
        $events->listen(SiteEndpointChecked::class, EvaluateSiteEndpointDown::class);
        $events->listen(SiteInspected::class, EvaluateSiteDebugEnabled::class);
        $events->listen(SiteInspected::class, EvaluateSiteNotInProduction::class);
        $events->listen(SiteInspected::class, EvaluateSiteClientUnauthorized::class);
        $events->listen(SiteInspected::class, EvaluateSiteClientIncompatible::class);
        $events->listen(RepositoriesCollected::class, EvaluateRepositoryMissing::class);
        $events->listen(RepositoryMissingIgnored::class, EvaluateRepositoryMissing::class);
        $events->listen(ComposerJsonChanged::class, EvaluateComposerWarning::class);
        $events->listen(ComposerLockChanged::class, EvaluateComposerWarning::class);
        $events->listen(ComposerJsonChanged::class, EvaluateComposerMinimumStability::class);
        $events->listen(ComposerJsonChanged::class, EvaluateDependabotCoverage::class);
        $events->listen(DependabotYmlChanged::class, EvaluateDependabotCoverage::class);
        $events->listen(PackageJsonChanged::class, EvaluateDependabotCoverage::class);
        $events->listen(SitesCollected::class, EvaluateSiteMissing::class);
        $events->listen(SiteMissingIgnored::class, EvaluateSiteMissing::class);
        $events->listen(RepositoriesCollected::class, EvaluateSiteBehind::class);
        $events->listen(SitesCollected::class, EvaluateSiteBehind::class);
        $events->listen(SitesCollected::class, EvaluateSiteDeploymentOverdue::class);
        $events->listen(SitesCollected::class, ResolveMissingSiteConditions::class);
    }
}
