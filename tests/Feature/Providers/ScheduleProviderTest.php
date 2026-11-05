<?php

declare(strict_types=1);

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Collection;
use Maarheeze\Compass\Collector\BuiltInDriver;
use Maarheeze\Compass\Collector\CollectorType;
use Maarheeze\Compass\Collector\ScheduleFrequency;
use Maarheeze\Compass\Console\CheckSiteEndpointsCommand;
use Maarheeze\Compass\Console\CollectCommand;

test('a collector of a source is scheduled as a collect command of that source and collector', function (): void {
    $sourceKey = fake()->slug();
    $collector = fake()->randomElement(CollectorType::cases())->value;
    $this->setConfiguration('compass.schedule', []);
    $this->setConfiguration('compass.sources', [
        $sourceKey => [
            'schedule' => [
                $collector => fake()->randomElement(ScheduleFrequency::cases()),
            ],
        ],
    ]);
    $schedule = $this->app->make(Schedule::class);

    $scheduledEvent = new Collection($schedule->events())->sole();

    expect($scheduledEvent->command)
        ->toEndWith(sprintf("%s '%s' '%s'", CollectCommand::SIGNATURE, $sourceKey, $collector));
});

test('each collector in the schedule of each source is scheduled', function (): void {
    $this->setConfiguration('compass.schedule', []);
    $this->setConfiguration('compass.sources', [
        fake()->unique()->slug() => [
            'schedule' => [
                CollectorType::REPOSITORIES->value => fake()->randomElement(ScheduleFrequency::cases()),
                CollectorType::PULL_REQUESTS->value => fake()->randomElement(ScheduleFrequency::cases()),
            ],
        ],
        fake()->unique()->slug() => [
            'schedule' => [
                CollectorType::SITES->value => fake()->randomElement(ScheduleFrequency::cases()),
            ],
        ],
    ]);
    $schedule = $this->app->make(Schedule::class);

    $scheduledEvents = $schedule->events();

    expect($scheduledEvents)
        ->toHaveCount(3);
});

test('a source without a schedule is not scheduled', function (): void {
    $this->setConfiguration('compass.schedule', []);
    $this->setConfiguration('compass.sources', [
        fake()->slug() => [
            'driver' => BuiltInDriver::GITHUB,
        ],
    ]);
    $schedule = $this->app->make(Schedule::class);

    $scheduledEvents = $schedule->events();

    expect($scheduledEvents)
        ->toBeEmpty();
});

test('a frequency is scheduled at its cron expression', function (
    ScheduleFrequency $scheduleFrequency,
    string $expression,
): void {
    $this->setConfiguration('compass.schedule', []);
    $this->setConfiguration('compass.sources', [
        fake()->slug() => [
            'schedule' => [
                fake()->randomElement(CollectorType::cases())->value => $scheduleFrequency,
            ],
        ],
    ]);
    $schedule = $this->app->make(Schedule::class);

    $scheduledEvent = new Collection($schedule->events())->sole();

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

test('the endpoint checks are scheduled as the endpoints check command', function (): void {
    $this->setConfiguration('compass.schedule', [
        'site_endpoints' => fake()->randomElement(ScheduleFrequency::cases()),
    ]);
    $this->setConfiguration('compass.sources', []);
    $schedule = $this->app->make(Schedule::class);

    $scheduledEvent = new Collection($schedule->events())->sole();

    expect($scheduledEvent->command)
        ->toEndWith(CheckSiteEndpointsCommand::SIGNATURE);
});

test('the endpoint checks are not scheduled when left out of the schedule', function (): void {
    $this->setConfiguration('compass.schedule', []);
    $this->setConfiguration('compass.sources', []);
    $schedule = $this->app->make(Schedule::class);

    $scheduledEvents = $schedule->events();

    expect($scheduledEvents)
        ->toBeEmpty();
});
