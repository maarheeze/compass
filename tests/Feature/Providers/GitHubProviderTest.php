<?php

declare(strict_types=1);

use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Collection;
use Maarheeze\Compass\Console\SyncGitHubCommand;

test('sync github is scheduled every hour', function (): void {
    $schedule = $this->app->make(Schedule::class);

    $scheduledEvent = new Collection($schedule->events())->sole(static function (Event $event): bool {
        return str_contains($event->command, SyncGitHubCommand::SIGNATURE);
    });

    expect($scheduledEvent->expression)
        ->toBe('0 * * * *');
});

test('sync github runs when its schedule is switched on', function (): void {
    $this->setConfiguration('compass.schedule.github', true);
    $schedule = $this->app->make(Schedule::class);

    $scheduledEvent = new Collection($schedule->events())->sole(static function (Event $event): bool {
        return str_contains($event->command, SyncGitHubCommand::SIGNATURE);
    });

    $filtersPass = $scheduledEvent->filtersPass($this->app);

    expect($filtersPass)
        ->toBeTrue();
});

test('sync github does not run when its schedule is switched off', function (): void {
    $this->setConfiguration('compass.schedule.github', false);
    $schedule = $this->app->make(Schedule::class);

    $scheduledEvent = new Collection($schedule->events())->sole(static function (Event $event): bool {
        return str_contains($event->command, SyncGitHubCommand::SIGNATURE);
    });

    $filtersPass = $scheduledEvent->filtersPass($this->app);

    expect($filtersPass)
        ->toBeFalse();
});
