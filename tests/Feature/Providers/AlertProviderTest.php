<?php

declare(strict_types=1);

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Testing\Fakes\EventFake;
use Maarheeze\Compass\Alerts\Events\ConditionPresent;
use Maarheeze\Compass\Alerts\Listeners\AlertRecorder;

test('alert recorder listens to condition present', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));

    $eventFake->assertListening(ConditionPresent::class, AlertRecorder::class);
});
