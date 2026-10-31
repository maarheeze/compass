<?php

declare(strict_types=1);

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Testing\Fakes\EventFake;
use Maarheeze\Compass\Alerts\Events\AlertOpened;
use Maarheeze\Compass\Alerts\Events\ConditionObserved;
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
use Maarheeze\Compass\Alerts\Listeners\ObservationHandler;
use Maarheeze\Compass\Alerts\Listeners\SendAlertNotifications;
use Maarheeze\Compass\Alerts\Listeners\SiteEndpoint\EvaluateSiteEndpointDown;
use Maarheeze\Compass\Collector\Events\ForgeRunSucceeded;
use Maarheeze\Compass\Collector\Events\GitHubRunSucceeded;
use Maarheeze\Compass\Collector\Events\RepositoryMissingIgnored;
use Maarheeze\Compass\Collector\Events\SiteEndpointChecked;
use Maarheeze\Compass\Collector\Events\SiteInspected;
use Maarheeze\Compass\Collector\Events\SiteMissingIgnored;

test('observation handler listens to condition observed', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));

    $eventFake->assertListening(ConditionObserved::class, ObservationHandler::class);
});

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

test('evaluate missing repository listens to github run succeeded', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));

    $eventFake->assertListening(GitHubRunSucceeded::class, EvaluateRepositoryMissing::class);
});

test('evaluate missing repository listens to repository missing ignored', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));

    $eventFake->assertListening(RepositoryMissingIgnored::class, EvaluateRepositoryMissing::class);
});

test('evaluate composer warning listens to github run succeeded', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));

    $eventFake->assertListening(GitHubRunSucceeded::class, EvaluateComposerWarning::class);
});

test('evaluate composer minimum stability listens to github run succeeded', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));

    $eventFake->assertListening(GitHubRunSucceeded::class, EvaluateComposerMinimumStability::class);
});

test('evaluate dependabot coverage listens to github run succeeded', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));

    $eventFake->assertListening(GitHubRunSucceeded::class, EvaluateDependabotCoverage::class);
});

test('evaluate missing site listens to forge run succeeded', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));

    $eventFake->assertListening(ForgeRunSucceeded::class, EvaluateSiteMissing::class);
});

test('evaluate missing site listens to site missing ignored', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));

    $eventFake->assertListening(SiteMissingIgnored::class, EvaluateSiteMissing::class);
});

test('evaluate site behind listens to github run succeeded', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));

    $eventFake->assertListening(GitHubRunSucceeded::class, EvaluateSiteBehind::class);
});

test('evaluate site behind listens to forge run succeeded', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));

    $eventFake->assertListening(ForgeRunSucceeded::class, EvaluateSiteBehind::class);
});

test('evaluate overdue deployment listens to forge run succeeded', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));

    $eventFake->assertListening(ForgeRunSucceeded::class, EvaluateSiteDeploymentOverdue::class);
});

test('resolve missing site conditions listens to forge run succeeded', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));

    $eventFake->assertListening(ForgeRunSucceeded::class, ResolveMissingSiteConditions::class);
});
