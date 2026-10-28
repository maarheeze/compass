<?php

declare(strict_types=1);

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Testing\Fakes\EventFake;
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

test('observation handler listens to condition observed', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));

    $eventFake->assertListening(ConditionObserved::class, ObservationHandler::class);
});

test('evaluate down site endpoint listens to site endpoint checked', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));

    $eventFake->assertListening(SiteEndpointChecked::class, EvaluateDownSiteEndpoint::class);
});

test('evaluate debug enabled listens to site inspected', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));

    $eventFake->assertListening(SiteInspected::class, EvaluateDebugEnabled::class);
});

test('evaluate non production listens to site inspected', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));

    $eventFake->assertListening(SiteInspected::class, EvaluateNonProduction::class);
});

test('evaluate unauthorized client listens to site inspected', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));

    $eventFake->assertListening(SiteInspected::class, EvaluateUnauthorizedClient::class);
});

test('evaluate incompatible client listens to site inspected', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));

    $eventFake->assertListening(SiteInspected::class, EvaluateIncompatibleClient::class);
});

test('evaluate missing repository listens to github run succeeded', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));

    $eventFake->assertListening(GitHubRunSucceeded::class, EvaluateMissingRepository::class);
});

test('evaluate missing repository listens to repository missing ignored', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));

    $eventFake->assertListening(RepositoryMissingIgnored::class, EvaluateMissingRepository::class);
});

test('evaluate composer warning listens to github run succeeded', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));

    $eventFake->assertListening(GitHubRunSucceeded::class, EvaluateComposerWarning::class);
});

test('evaluate missing site listens to forge run succeeded', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));

    $eventFake->assertListening(ForgeRunSucceeded::class, EvaluateMissingSite::class);
});

test('evaluate missing site listens to site missing ignored', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));

    $eventFake->assertListening(SiteMissingIgnored::class, EvaluateMissingSite::class);
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

    $eventFake->assertListening(ForgeRunSucceeded::class, EvaluateOverdueDeployment::class);
});

test('evaluate down site endpoint listens to forge run succeeded', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));

    $eventFake->assertListening(ForgeRunSucceeded::class, EvaluateDownSiteEndpoint::class);
});

test('evaluate debug enabled listens to forge run succeeded', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));

    $eventFake->assertListening(ForgeRunSucceeded::class, EvaluateDebugEnabled::class);
});

test('evaluate non production listens to forge run succeeded', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));

    $eventFake->assertListening(ForgeRunSucceeded::class, EvaluateNonProduction::class);
});

test('evaluate unauthorized client listens to forge run succeeded', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));

    $eventFake->assertListening(ForgeRunSucceeded::class, EvaluateUnauthorizedClient::class);
});

test('evaluate incompatible client listens to forge run succeeded', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));

    $eventFake->assertListening(ForgeRunSucceeded::class, EvaluateIncompatibleClient::class);
});
