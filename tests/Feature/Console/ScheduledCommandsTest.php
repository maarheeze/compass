<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Support\Testing\Fakes\BusFake;
use Maarheeze\Compass\Collector\TaskType;
use Maarheeze\Compass\Console\CheckSiteEndpointsCommand;
use Maarheeze\Compass\Console\ScheduledCommands;
use Maarheeze\Compass\Models\Run;
use Psr\Clock\ClockInterface;
use Symfony\Component\Clock\MockClock;

test('all lists a scheduled command of compass without the php binary and artisan', function (): void {
    $schedule = new Schedule();
    $schedule->command(CheckSiteEndpointsCommand::SIGNATURE);
    $this->app->instance(Schedule::class, $schedule);

    $scheduledCommands = $this->app->make(ScheduledCommands::class);
    $allScheduledCommands = $scheduledCommands->all();

    expect($allScheduledCommands->sole()->command)
        ->toBe(CheckSiteEndpointsCommand::SIGNATURE);
});

test('all leaves out a scheduled command that is not of compass', function (): void {
    $schedule = new Schedule();
    $schedule->command(fake()->slug());
    $this->app->instance(Schedule::class, $schedule);

    $scheduledCommands = $this->app->make(ScheduledCommands::class);
    $allScheduledCommands = $scheduledCommands->all();

    expect($allScheduledCommands)
        ->toBeEmpty();
});

test('all names a scheduled command by the mutex name of its event', function (): void {
    $schedule = new Schedule();
    $event = $schedule->command(CheckSiteEndpointsCommand::SIGNATURE);
    $this->app->instance(Schedule::class, $schedule);

    $scheduledCommands = $this->app->make(ScheduledCommands::class);
    $allScheduledCommands = $scheduledCommands->all();

    expect($allScheduledCommands->sole()->mutexName)
        ->toBe($event->mutexName());
});

test('all gives the next run of a scheduled command after the current time', function (): void {
    $now = CarbonImmutable::instance(fake()->dateTime());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $schedule = new Schedule();
    $schedule
        ->command(CheckSiteEndpointsCommand::SIGNATURE)
        ->daily();
    $this->app->instance(Schedule::class, $schedule);

    $scheduledCommands = $this->app->make(ScheduledCommands::class);
    $allScheduledCommands = $scheduledCommands->all();

    expect($allScheduledCommands->sole()->nextRunAt->getTimestamp())
        ->toBe($now->addDay()->startOfDay()->getTimestamp());
});

test('run runs the scheduled command of the mutex name', function (): void {
    $this->app->instance(QueueingDispatcher::class, new BusFake($this->app->make(QueueingDispatcher::class)));
    $schedule = new Schedule();
    $event = $schedule->command(CheckSiteEndpointsCommand::SIGNATURE);
    $this->app->instance(Schedule::class, $schedule);

    $scheduledCommands = $this->app->make(ScheduledCommands::class);
    $scheduledCommands->run($event->mutexName());

    $this->assertDatabaseHas(Run::class, [
        'task' => TaskType::SITE_ENDPOINTS,
    ]);
});
