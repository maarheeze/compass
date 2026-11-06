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
use Maarheeze\Compass\Alerts\Listeners\Composer\EvaluateComposerWarningOnComposerJsonChanged;
use Maarheeze\Compass\Alerts\Listeners\Composer\EvaluateComposerWarningOnComposerLockChanged;
use Maarheeze\Compass\Alerts\Listeners\Forge\EvaluateSiteBehindOnRepositoriesCollected;
use Maarheeze\Compass\Alerts\Listeners\Forge\EvaluateSiteBehindOnSitesCollected;
use Maarheeze\Compass\Alerts\Listeners\Forge\EvaluateSiteDeploymentOverdue;
use Maarheeze\Compass\Alerts\Listeners\Forge\EvaluateSiteMissingOnSiteMissingIgnored;
use Maarheeze\Compass\Alerts\Listeners\Forge\EvaluateSiteMissingOnSitesCollected;
use Maarheeze\Compass\Alerts\Listeners\Forge\ResolveMissingSiteConditions;
use Maarheeze\Compass\Alerts\Listeners\GitHub\EvaluateDependabotCoverageOnComposerJsonChanged;
use Maarheeze\Compass\Alerts\Listeners\GitHub\EvaluateDependabotCoverageOnDependabotYmlChanged;
use Maarheeze\Compass\Alerts\Listeners\GitHub\EvaluateDependabotCoverageOnPackageJsonChanged;
use Maarheeze\Compass\Alerts\Listeners\GitHub\EvaluateRepositoryMissingOnRepositoriesCollected;
use Maarheeze\Compass\Alerts\Listeners\GitHub\EvaluateRepositoryMissingOnRepositoryMissingIgnored;
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
        $events->listen(RepositoriesCollected::class, EvaluateRepositoryMissingOnRepositoriesCollected::class);
        $events->listen(RepositoryMissingIgnored::class, EvaluateRepositoryMissingOnRepositoryMissingIgnored::class);
        $events->listen(ComposerJsonChanged::class, EvaluateComposerWarningOnComposerJsonChanged::class);
        $events->listen(ComposerLockChanged::class, EvaluateComposerWarningOnComposerLockChanged::class);
        $events->listen(ComposerJsonChanged::class, EvaluateComposerMinimumStability::class);
        $events->listen(ComposerJsonChanged::class, EvaluateDependabotCoverageOnComposerJsonChanged::class);
        $events->listen(DependabotYmlChanged::class, EvaluateDependabotCoverageOnDependabotYmlChanged::class);
        $events->listen(PackageJsonChanged::class, EvaluateDependabotCoverageOnPackageJsonChanged::class);
        $events->listen(SitesCollected::class, EvaluateSiteMissingOnSitesCollected::class);
        $events->listen(SiteMissingIgnored::class, EvaluateSiteMissingOnSiteMissingIgnored::class);
        $events->listen(RepositoriesCollected::class, EvaluateSiteBehindOnRepositoriesCollected::class);
        $events->listen(SitesCollected::class, EvaluateSiteBehindOnSitesCollected::class);
        $events->listen(SitesCollected::class, EvaluateSiteDeploymentOverdue::class);
        $events->listen(SitesCollected::class, ResolveMissingSiteConditions::class);
    }
}
