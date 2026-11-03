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

test('evaluate missing repository listens to repositories collected', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));

    $eventFake->assertListening(RepositoriesCollected::class, EvaluateRepositoryMissing::class);
});

test('evaluate missing repository listens to repository missing ignored', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));

    $eventFake->assertListening(RepositoryMissingIgnored::class, EvaluateRepositoryMissing::class);
});

test('evaluate composer warning listens to composer json changed', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));

    $eventFake->assertListening(ComposerJsonChanged::class, EvaluateComposerWarning::class);
});

test('evaluate composer warning listens to composer lock changed', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));

    $eventFake->assertListening(ComposerLockChanged::class, EvaluateComposerWarning::class);
});

test('evaluate composer minimum stability listens to composer json changed', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));

    $eventFake->assertListening(ComposerJsonChanged::class, EvaluateComposerMinimumStability::class);
});

test('evaluate dependabot coverage listens to composer json changed', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));

    $eventFake->assertListening(ComposerJsonChanged::class, EvaluateDependabotCoverage::class);
});

test('evaluate dependabot coverage listens to dependabot yml changed', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));

    $eventFake->assertListening(DependabotYmlChanged::class, EvaluateDependabotCoverage::class);
});

test('evaluate dependabot coverage listens to package json changed', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));

    $eventFake->assertListening(PackageJsonChanged::class, EvaluateDependabotCoverage::class);
});

test('evaluate missing site listens to sites collected', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));

    $eventFake->assertListening(SitesCollected::class, EvaluateSiteMissing::class);
});

test('evaluate missing site listens to site missing ignored', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));

    $eventFake->assertListening(SiteMissingIgnored::class, EvaluateSiteMissing::class);
});

test('evaluate site behind listens to repositories collected', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));

    $eventFake->assertListening(RepositoriesCollected::class, EvaluateSiteBehind::class);
});

test('evaluate site behind listens to sites collected', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));

    $eventFake->assertListening(SitesCollected::class, EvaluateSiteBehind::class);
});

test('evaluate overdue deployment listens to sites collected', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));

    $eventFake->assertListening(SitesCollected::class, EvaluateSiteDeploymentOverdue::class);
});

test('resolve missing site conditions listens to sites collected', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));

    $eventFake->assertListening(SitesCollected::class, ResolveMissingSiteConditions::class);
});
