<?php

declare(strict_types=1);

use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Collection;
use Maarheeze\Compass\Console\CheckSiteEndpointsCommand;

test('check site endpoints is scheduled every fifteen minutes', function (): void {
    $schedule = $this->app->make(Schedule::class);

    $scheduledEvent = new Collection($schedule->events())->sole(static function (Event $event): bool {
        return str_contains($event->command, CheckSiteEndpointsCommand::SIGNATURE);
    });

    expect($scheduledEvent->expression)
        ->toBe('*/15 * * * *');
});

test('check site endpoints runs when its schedule is switched on', function (): void {
    $this->setConfiguration('compass.schedule.site_endpoints', true);
    $schedule = $this->app->make(Schedule::class);

    $scheduledEvent = new Collection($schedule->events())->sole(static function (Event $event): bool {
        return str_contains($event->command, CheckSiteEndpointsCommand::SIGNATURE);
    });

    $filtersPass = $scheduledEvent->filtersPass($this->app);

    expect($filtersPass)
        ->toBeTrue();
});

test('check site endpoints does not run when its schedule is switched off', function (): void {
    $this->setConfiguration('compass.schedule.site_endpoints', false);
    $schedule = $this->app->make(Schedule::class);

    $scheduledEvent = new Collection($schedule->events())->sole(static function (Event $event): bool {
        return str_contains($event->command, CheckSiteEndpointsCommand::SIGNATURE);
    });

    $filtersPass = $scheduledEvent->filtersPass($this->app);

    expect($filtersPass)
        ->toBeFalse();
});
