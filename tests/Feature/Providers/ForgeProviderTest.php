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
