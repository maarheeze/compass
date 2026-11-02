<?php

declare(strict_types=1);

use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Collection;
use Maarheeze\Compass\Collector\ScheduleFrequency;
use Maarheeze\Compass\Console\CheckSiteEndpointsCommand;
use Maarheeze\Compass\Console\SyncForgeCommand;
use Maarheeze\Compass\Console\SyncGitHubCommand;

test('a task is scheduled at the frequency configured for it', function (string $key, string $signature): void {
    $this->setConfiguration($key, ScheduleFrequency::DAILY);
    $schedule = $this->app->make(Schedule::class);

    $scheduledEvent = new Collection($schedule->events())->sole(static function (Event $event) use ($signature): bool {
        return str_contains($event->command, $signature);
    });

    expect($scheduledEvent->expression)
        ->toBe('0 0 * * *');
})->with([
    'forge' => ['compass.schedule.forge', SyncForgeCommand::SIGNATURE],
    'github' => ['compass.schedule.github', SyncGitHubCommand::SIGNATURE],
    'site endpoints' => ['compass.schedule.site_endpoints', CheckSiteEndpointsCommand::SIGNATURE],
]);

test('a task scheduled never is not scheduled', function (string $key, string $signature): void {
    $this->setConfiguration($key, ScheduleFrequency::NEVER);
    $schedule = $this->app->make(Schedule::class);

    $scheduledEvents = new Collection($schedule->events())->filter(
        static function (Event $event) use ($signature): bool {
            return str_contains($event->command, $signature);
        },
    );

    expect($scheduledEvents)
        ->toBeEmpty();
})->with([
    'forge' => ['compass.schedule.forge', SyncForgeCommand::SIGNATURE],
    'github' => ['compass.schedule.github', SyncGitHubCommand::SIGNATURE],
    'site endpoints' => ['compass.schedule.site_endpoints', CheckSiteEndpointsCommand::SIGNATURE],
]);

test('a frequency is scheduled at its cron expression', function (
    ScheduleFrequency $scheduleFrequency,
    string $expression,
): void {
    $this->setConfiguration('compass.schedule.forge', $scheduleFrequency);
    $schedule = $this->app->make(Schedule::class);

    $scheduledEvent = new Collection($schedule->events())->sole(static function (Event $event): bool {
        return str_contains($event->command, SyncForgeCommand::SIGNATURE);
    });

    expect($scheduledEvent->expression)
        ->toBe($expression);
})->with([
    'daily' => [ScheduleFrequency::DAILY, '0 0 * * *'],
    'every fifteen minutes' => [ScheduleFrequency::EVERY_FIFTEEN_MINUTES, '*/15 * * * *'],
    'every five minutes' => [ScheduleFrequency::EVERY_FIVE_MINUTES, '*/5 * * * *'],
    'every thirty minutes' => [ScheduleFrequency::EVERY_THIRTY_MINUTES, '*/30 * * * *'],
    'hourly' => [ScheduleFrequency::HOURLY, '0 * * * *'],
    'weekly' => [ScheduleFrequency::WEEKLY, '0 0 * * 0'],
]);
