<?php

declare(strict_types=1);

use Illuminate\Console\Scheduling\Schedule;
use Maarheeze\Compass\Console\SyncForgeCommand;
use Maarheeze\Compass\Console\SyncGitHubCommand;

test('sync forge is scheduled every hour', function (): void {
    $schedule = $this->app->make(Schedule::class);
    $scheduledEvent = $schedule->events()[1];

    expect($scheduledEvent->command)
        ->toContain(SyncForgeCommand::SIGNATURE)
        ->and($scheduledEvent->expression)
        ->toBe('0 * * * *');
});

test('sync github is scheduled every hour', function (): void {
    $schedule = $this->app->make(Schedule::class);
    $scheduledEvent = $schedule->events()[0];

    expect($scheduledEvent->command)
        ->toContain(SyncGitHubCommand::SIGNATURE)
        ->and($scheduledEvent->expression)
        ->toBe('0 * * * *');
});
