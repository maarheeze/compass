<?php

declare(strict_types=1);

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Testing\Fakes\EventFake;
use Maarheeze\Compass\Alerts\Events\ConditionObserved;
use Maarheeze\Compass\Alerts\Listeners\EvaluateDebugEnabled;
use Maarheeze\Compass\Alerts\Listeners\EvaluateDownSiteEndpoint;
use Maarheeze\Compass\Alerts\Listeners\ObservationHandler;
use Maarheeze\Compass\Collector\Events\SiteEndpointChecked;
use Maarheeze\Compass\Collector\Events\SiteInspected;

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
