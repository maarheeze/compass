<?php

declare(strict_types=1);

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Testing\Fakes\EventFake;
use Maarheeze\Compass\Alerts\Events\ConditionObserved;
use Maarheeze\Compass\Alerts\Listeners\EvaluateComposerWarnings;
use Maarheeze\Compass\Alerts\Listeners\EvaluateDebugEnabled;
use Maarheeze\Compass\Alerts\Listeners\EvaluateDownSiteEndpoint;
use Maarheeze\Compass\Alerts\Listeners\EvaluateIncompatibleClient;
use Maarheeze\Compass\Alerts\Listeners\EvaluateMissingRepositories;
use Maarheeze\Compass\Alerts\Listeners\EvaluateMissingSites;
use Maarheeze\Compass\Alerts\Listeners\EvaluateNonProduction;
use Maarheeze\Compass\Alerts\Listeners\EvaluateSitesBehind;
use Maarheeze\Compass\Alerts\Listeners\EvaluateUnauthorizedClient;
use Maarheeze\Compass\Alerts\Listeners\ObservationHandler;
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

test('evaluate missing repositories listens to github run succeeded', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));

    $eventFake->assertListening(GitHubRunSucceeded::class, EvaluateMissingRepositories::class);
});

test('evaluate missing repositories listens to repository missing ignored', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));

    $eventFake->assertListening(RepositoryMissingIgnored::class, EvaluateMissingRepositories::class);
});

test('evaluate composer warnings listens to github run succeeded', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));

    $eventFake->assertListening(GitHubRunSucceeded::class, EvaluateComposerWarnings::class);
});

test('evaluate missing sites listens to forge run succeeded', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));

    $eventFake->assertListening(ForgeRunSucceeded::class, EvaluateMissingSites::class);
});

test('evaluate missing sites listens to site missing ignored', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));

    $eventFake->assertListening(SiteMissingIgnored::class, EvaluateMissingSites::class);
});

test('evaluate sites behind listens to github run succeeded', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));

    $eventFake->assertListening(GitHubRunSucceeded::class, EvaluateSitesBehind::class);
});

test('evaluate sites behind listens to forge run succeeded', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));

    $eventFake->assertListening(ForgeRunSucceeded::class, EvaluateSitesBehind::class);
});
