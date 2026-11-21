<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Console\Scheduling\Schedule;
use Maarheeze\Compass\Collector\CollectorSchedule;
use Maarheeze\Compass\Collector\CollectorType;
use Maarheeze\Compass\Console\CollectCommand;
use Psr\Clock\ClockInterface;
use Symfony\Component\Clock\MockClock;

test('next run at gives the next run of the collector of the source', function (): void {
    $now = CarbonImmutable::instance(fake()->dateTime());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $source = fake()->slug();
    $collectorType = fake()->randomElement(CollectorType::cases());
    $schedule = new Schedule();
    $schedule
        ->command(CollectCommand::SIGNATURE, [$source, $collectorType->value])
        ->daily();
    $this->app->instance(Schedule::class, $schedule);

    $collectorSchedule = $this->app->make(CollectorSchedule::class);
    $nextRunAt = $collectorSchedule->nextRunAt($source, $collectorType);

    expect($nextRunAt?->getTimestamp())
        ->toBe($now->addDay()
            ->startOfDay()
            ->getTimestamp());
});

test('next run at is null when the collector of the source is not scheduled', function (): void {
    $collectorType = fake()->randomElement(CollectorType::cases());
    $schedule = new Schedule();
    $schedule->command(CollectCommand::SIGNATURE, [fake()->unique()->slug(), $collectorType->value]);
    $this->app->instance(Schedule::class, $schedule);

    $collectorSchedule = $this->app->make(CollectorSchedule::class);
    $nextRunAt = $collectorSchedule->nextRunAt(fake()->unique()->slug(), $collectorType);

    expect($nextRunAt)
        ->toBeNull();
});

test('next run at is null when only another collector of the source is scheduled', function (): void {
    $source = fake()->slug();
    [$collectorType, $otherCollectorType] = fake()->randomElements(CollectorType::cases(), 2);
    $schedule = new Schedule();
    $schedule->command(CollectCommand::SIGNATURE, [$source, $otherCollectorType->value]);
    $this->app->instance(Schedule::class, $schedule);

    $collectorSchedule = $this->app->make(CollectorSchedule::class);
    $nextRunAt = $collectorSchedule->nextRunAt($source, $collectorType);

    expect($nextRunAt)
        ->toBeNull();
});
