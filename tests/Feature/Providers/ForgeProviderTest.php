<?php

declare(strict_types=1);

use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Collection;
use Maarheeze\Compass\Console\SyncForgeCommand;

test('sync forge is scheduled every hour', function (): void {
    $schedule = $this->app->make(Schedule::class);

    $scheduledEvent = new Collection($schedule->events())->sole(static function (Event $event): bool {
        return str_contains($event->command, SyncForgeCommand::SIGNATURE);
    });

    expect($scheduledEvent->expression)
        ->toBe('0 * * * *');
});

test('sync forge runs when its schedule is switched on', function (): void {
    $this->setConfiguration('compass.schedule.forge', true);
    $schedule = $this->app->make(Schedule::class);

    $scheduledEvent = new Collection($schedule->events())->sole(static function (Event $event): bool {
        return str_contains($event->command, SyncForgeCommand::SIGNATURE);
    });

    $filtersPass = $scheduledEvent->filtersPass($this->app);

    expect($filtersPass)
        ->toBeTrue();
});

test('sync forge does not run when its schedule is switched off', function (): void {
    $this->setConfiguration('compass.schedule.forge', false);
    $schedule = $this->app->make(Schedule::class);

    $scheduledEvent = new Collection($schedule->events())->sole(static function (Event $event): bool {
        return str_contains($event->command, SyncForgeCommand::SIGNATURE);
    });

    $filtersPass = $scheduledEvent->filtersPass($this->app);

    expect($filtersPass)
        ->toBeFalse();
});
