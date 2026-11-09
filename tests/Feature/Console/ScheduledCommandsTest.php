<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Console\ClosureCommand;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Maarheeze\Compass\Console\CheckSiteEndpointsCommand;
use Maarheeze\Compass\Console\ScheduledCommands;
use Psr\Clock\ClockInterface;
use Symfony\Component\Clock\MockClock;
use Webmozart\Assert\Assert;

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

test('all leaves out a scheduled event that is not an artisan command', function (): void {
    $schedule = new Schedule();
    $schedule->exec(CheckSiteEndpointsCommand::SIGNATURE);
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
        ->toBe($now->addDay()
            ->startOfDay()
            ->getTimestamp());
});

test('run runs the scheduled command of the mutex name with its arguments', function (): void {
    $recordedArguments = [];
    $kernel = Assert::isInstanceOf($this->app->make(Kernel::class), ConsoleKernel::class);
    $kernel->registerCommand(new ClosureCommand(
        'compass:fake-command {argument}',
        function (string $argument) use (&$recordedArguments): void {
            $recordedArguments[] = $argument;
        },
    ));
    $argument = fake()->unique()->words(2, true);
    $schedule = new Schedule();
    $event = $schedule->command('compass:fake-command', [$argument]);
    $schedule->command('compass:fake-command', [fake()->unique()->words(2, true)]);
    $this->app->instance(Schedule::class, $schedule);

    $scheduledCommands = $this->app->make(ScheduledCommands::class);
    $scheduledCommands->run($event->mutexName());

    expect($recordedArguments)
        ->toBe([$argument]);
});
