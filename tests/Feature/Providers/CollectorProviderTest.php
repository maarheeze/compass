<?php

declare(strict_types=1);

use Illuminate\Console\Scheduling\Schedule;
use Maarheeze\Compass\Console\SyncGitHubCommand;

test('sync github is scheduled every hour', function (): void {
    $schedule = $this->app->make(Schedule::class);
    $scheduledEvent = $schedule->events()[0];

    expect($schedule->events())
        ->toHaveCount(1)
        ->and($scheduledEvent->command)
        ->toContain(SyncGitHubCommand::SIGNATURE)
        ->and($scheduledEvent->expression)
        ->toBe('0 * * * *');
});
