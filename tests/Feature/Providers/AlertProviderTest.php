<?php

declare(strict_types=1);

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Testing\Fakes\EventFake;
use Maarheeze\Compass\Alerts\Events\AlertOpened;
use Maarheeze\Compass\Alerts\Listeners\Client\EvaluateSiteClientIncompatible;
use Maarheeze\Compass\Alerts\Listeners\Client\EvaluateSiteClientUnauthorized;
use Maarheeze\Compass\Alerts\Listeners\Client\EvaluateSiteDebugEnabled;
use Maarheeze\Compass\Alerts\Listeners\Client\EvaluateSiteNotInProduction;
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
use Maarheeze\Compass\Alerts\Listeners\Site\ResolveMissingSiteConditions;
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

test('send alert notifications listens to alert opened', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));

    $eventFake->assertListening(AlertOpened::class, SendAlertNotifications::class);
});

test('evaluate down site endpoint listens to site endpoint checked', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));

    $eventFake->assertListening(SiteEndpointChecked::class, EvaluateSiteEndpointDown::class);
});

test('evaluate debug enabled listens to site inspected', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));

    $eventFake->assertListening(SiteInspected::class, EvaluateSiteDebugEnabled::class);
});

test('evaluate non production listens to site inspected', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));

    $eventFake->assertListening(SiteInspected::class, EvaluateSiteNotInProduction::class);
});

test('evaluate unauthorized client listens to site inspected', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));

    $eventFake->assertListening(SiteInspected::class, EvaluateSiteClientUnauthorized::class);
});

test('evaluate incompatible client listens to site inspected', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));

    $eventFake->assertListening(SiteInspected::class, EvaluateSiteClientIncompatible::class);
});

test('evaluate repository missing on repositories collected listens to repositories collected', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));

    $eventFake->assertListening(RepositoriesCollected::class, EvaluateRepositoryMissingOnRepositoriesCollected::class);
});

test('evaluate repository missing on repository missing ignored listens to repository missing ignored', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));

    $eventFake->assertListening(RepositoryMissingIgnored::class, EvaluateRepositoryMissingOnRepositoryMissingIgnored::class);
});

test('evaluate composer warning on composer json changed listens to composer json changed', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));

    $eventFake->assertListening(ComposerJsonChanged::class, EvaluateComposerWarningOnComposerJsonChanged::class);
});

test('evaluate composer warning on composer lock changed listens to composer lock changed', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));

    $eventFake->assertListening(ComposerLockChanged::class, EvaluateComposerWarningOnComposerLockChanged::class);
});

test('evaluate composer minimum stability listens to composer json changed', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));

    $eventFake->assertListening(ComposerJsonChanged::class, EvaluateComposerMinimumStability::class);
});

test('evaluate dependabot coverage on composer json changed listens to composer json changed', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));

    $eventFake->assertListening(ComposerJsonChanged::class, EvaluateDependabotCoverageOnComposerJsonChanged::class);
});

test('evaluate dependabot coverage on dependabot yml changed listens to dependabot yml changed', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));

    $eventFake->assertListening(DependabotYmlChanged::class, EvaluateDependabotCoverageOnDependabotYmlChanged::class);
});

test('evaluate dependabot coverage on package json changed listens to package json changed', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));

    $eventFake->assertListening(PackageJsonChanged::class, EvaluateDependabotCoverageOnPackageJsonChanged::class);
});

test('evaluate site missing on sites collected listens to sites collected', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));

    $eventFake->assertListening(SitesCollected::class, EvaluateSiteMissingOnSitesCollected::class);
});

test('evaluate site missing on site missing ignored listens to site missing ignored', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));

    $eventFake->assertListening(SiteMissingIgnored::class, EvaluateSiteMissingOnSiteMissingIgnored::class);
});

test('evaluate site behind on repositories collected listens to repositories collected', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));

    $eventFake->assertListening(RepositoriesCollected::class, EvaluateSiteBehindOnRepositoriesCollected::class);
});

test('evaluate site behind on sites collected listens to sites collected', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));

    $eventFake->assertListening(SitesCollected::class, EvaluateSiteBehindOnSitesCollected::class);
});

test('evaluate overdue deployment listens to sites collected', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));

    $eventFake->assertListening(SitesCollected::class, EvaluateSiteDeploymentOverdue::class);
});

test('resolve missing site conditions listens to sites collected', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));

    $eventFake->assertListening(SitesCollected::class, ResolveMissingSiteConditions::class);
});
