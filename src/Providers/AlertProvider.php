<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Providers;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\ServiceProvider;
use Maarheeze\Compass\Alerts\AlertTypes;
use Maarheeze\Compass\Alerts\Events\AlertHistoryEntryCreated;
use Maarheeze\Compass\Alerts\Events\AlertOpened;
use Maarheeze\Compass\Alerts\Listeners\BroadcastAlertHistoryEntry;
use Maarheeze\Compass\Alerts\Listeners\Composer\EvaluateComposerMinimumStability;
use Maarheeze\Compass\Alerts\Listeners\Composer\EvaluateComposerWarningOnComposerJsonChanged;
use Maarheeze\Compass\Alerts\Listeners\Composer\EvaluateComposerWarningOnComposerLockChanged;
use Maarheeze\Compass\Alerts\Listeners\Dependabot\EvaluateDependabotCoverageOnComposerJsonChanged;
use Maarheeze\Compass\Alerts\Listeners\Dependabot\EvaluateDependabotCoverageOnDependabotYmlChanged;
use Maarheeze\Compass\Alerts\Listeners\Dependabot\EvaluateDependabotCoverageOnPackageJsonChanged;
use Maarheeze\Compass\Alerts\Listeners\Repository\EvaluateRepositoryMissingOnRepositoriesCollected;
use Maarheeze\Compass\Alerts\Listeners\Repository\EvaluateRepositoryMissingOnRepositoryMissingIgnored;
use Maarheeze\Compass\Alerts\Listeners\SendAlertNotifications;
use Maarheeze\Compass\Alerts\Listeners\Site\EvaluateSiteBehindOnRepositoriesCollected;
use Maarheeze\Compass\Alerts\Listeners\Site\EvaluateSiteBehindOnSitesCollected;
use Maarheeze\Compass\Alerts\Listeners\Site\EvaluateSiteDeploymentOverdue;
use Maarheeze\Compass\Alerts\Listeners\Site\EvaluateSiteMissingOnSiteMissingIgnored;
use Maarheeze\Compass\Alerts\Listeners\Site\EvaluateSiteMissingOnSitesCollected;
use Maarheeze\Compass\Alerts\Listeners\SiteEndpoint\EvaluateSiteEndpointDown;
use Maarheeze\Compass\Alerts\Listeners\SiteReport\EvaluateSiteDebugEnabled;
use Maarheeze\Compass\Alerts\Listeners\SiteReport\EvaluateSiteNotInProduction;
use Maarheeze\Compass\Alerts\Listeners\SiteReport\EvaluateSiteReportIncompatible;
use Maarheeze\Compass\Alerts\Listeners\SiteReport\EvaluateSiteReportUnauthorized;
use Maarheeze\Compass\Alerts\Types\ComposerLibraryWithLock;
use Maarheeze\Compass\Alerts\Types\ComposerMinimumStabilityUnstable;
use Maarheeze\Compass\Alerts\Types\ComposerProjectWithoutLock;
use Maarheeze\Compass\Alerts\Types\ComposerTypeMissing;
use Maarheeze\Compass\Alerts\Types\ComposerTypeUnknown;
use Maarheeze\Compass\Alerts\Types\DependabotComposerMissing;
use Maarheeze\Compass\Alerts\Types\DependabotNpmMissing;
use Maarheeze\Compass\Alerts\Types\RepositoryMissing;
use Maarheeze\Compass\Alerts\Types\SiteBehind;
use Maarheeze\Compass\Alerts\Types\SiteDebugEnabled;
use Maarheeze\Compass\Alerts\Types\SiteDeploymentOverdue;
use Maarheeze\Compass\Alerts\Types\SiteEndpointDown;
use Maarheeze\Compass\Alerts\Types\SiteMissing;
use Maarheeze\Compass\Alerts\Types\SiteNotInProduction;
use Maarheeze\Compass\Alerts\Types\SiteReportIncompatible;
use Maarheeze\Compass\Alerts\Types\SiteReportUnauthorized;
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
use Maarheeze\Compass\Models\Alert;
use Maarheeze\Compass\Models\AlertHistoryEntry;
use Maarheeze\Compass\Models\NotificationSubscription;
use Maarheeze\Compass\Models\Scopes\RegisteredAlertTypes;

class AlertProvider extends ServiceProvider
{
    public function boot(AlertTypes $alertTypes, Dispatcher $events): void
    {
        $alertTypes->register(new RepositoryMissing());
        $alertTypes->register(new ComposerLibraryWithLock());
        $alertTypes->register(new ComposerMinimumStabilityUnstable());
        $alertTypes->register(new ComposerProjectWithoutLock());
        $alertTypes->register(new ComposerTypeMissing());
        $alertTypes->register(new ComposerTypeUnknown());
        $alertTypes->register(new DependabotComposerMissing());
        $alertTypes->register(new DependabotNpmMissing());
        $alertTypes->register(new SiteEndpointDown());
        $alertTypes->register(new SiteBehind());
        $alertTypes->register(new SiteDeploymentOverdue());
        $alertTypes->register(new SiteMissing());
        $alertTypes->register(new SiteDebugEnabled());
        $alertTypes->register(new SiteNotInProduction());
        $alertTypes->register(new SiteReportIncompatible());
        $alertTypes->register(new SiteReportUnauthorized());

        Alert::addGlobalScope(new RegisteredAlertTypes($alertTypes));
        NotificationSubscription::addGlobalScope(new RegisteredAlertTypes($alertTypes));

        $events->listen(AlertHistoryEntryCreated::class, BroadcastAlertHistoryEntry::class);
        $events->listen(AlertOpened::class, SendAlertNotifications::class);
        $events->listen(SiteEndpointChecked::class, EvaluateSiteEndpointDown::class);
        $events->listen(SiteInspected::class, EvaluateSiteDebugEnabled::class);
        $events->listen(SiteInspected::class, EvaluateSiteNotInProduction::class);
        $events->listen(SiteInspected::class, EvaluateSiteReportUnauthorized::class);
        $events->listen(SiteInspected::class, EvaluateSiteReportIncompatible::class);
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

        AlertHistoryEntry::created(static function (AlertHistoryEntry $alertHistoryEntry) use ($events): void {
            $events->dispatch(new AlertHistoryEntryCreated($alertHistoryEntry));
        });
    }

    public function register(): void
    {
        $this->app->singleton(AlertTypes::class);
    }
}
