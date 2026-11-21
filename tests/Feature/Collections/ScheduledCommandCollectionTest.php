<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Support\ItemNotFoundException;
use Maarheeze\Compass\Collections\ScheduledCommandCollection;
use Maarheeze\Compass\Scheduling\ScheduledCommand;

test('mutex names are the mutex names of the scheduled commands', function (): void {
    $scheduledCommand = new ScheduledCommand(
        fake()->slug(),
        fake()->unique()->sha1(),
        CarbonImmutable::instance(fake()->dateTime()),
    );
    $otherScheduledCommand = new ScheduledCommand(
        fake()->slug(),
        fake()->unique()->sha1(),
        CarbonImmutable::instance(fake()->dateTime()),
    );

    $scheduledCommandCollection = new ScheduledCommandCollection([$scheduledCommand, $otherScheduledCommand]);
    $mutexNames = $scheduledCommandCollection->mutexNames();

    expect($mutexNames)
        ->toBe([$scheduledCommand->mutexName, $otherScheduledCommand->mutexName]);
});

test('with command is the scheduled command of the command', function (): void {
    $scheduledCommand = new ScheduledCommand(
        fake()->unique()->slug(),
        fake()->sha1(),
        CarbonImmutable::instance(fake()->dateTime()),
    );
    $otherScheduledCommand = new ScheduledCommand(
        fake()->unique()->slug(),
        fake()->sha1(),
        CarbonImmutable::instance(fake()->dateTime()),
    );

    $scheduledCommandCollection = new ScheduledCommandCollection([$otherScheduledCommand, $scheduledCommand]);
    $scheduledCommandWithCommand = $scheduledCommandCollection->withCommand($scheduledCommand->command);

    expect($scheduledCommandWithCommand)
        ->toBe($scheduledCommand);
});

test('with command is null for a command of no scheduled command', function (): void {
    $scheduledCommandCollection = new ScheduledCommandCollection([
        new ScheduledCommand(fake()->unique()->slug(), fake()->sha1(), CarbonImmutable::instance(fake()->dateTime())),
    ]);

    $scheduledCommandWithCommand = $scheduledCommandCollection->withCommand(fake()->unique()->slug());

    expect($scheduledCommandWithCommand)
        ->toBeNull();
});

test('with mutex name is the scheduled command of the mutex name', function (): void {
    $scheduledCommand = new ScheduledCommand(
        fake()->slug(),
        fake()->unique()->sha1(),
        CarbonImmutable::instance(fake()->dateTime()),
    );
    $otherScheduledCommand = new ScheduledCommand(
        fake()->slug(),
        fake()->unique()->sha1(),
        CarbonImmutable::instance(fake()->dateTime()),
    );

    $scheduledCommandCollection = new ScheduledCommandCollection([$otherScheduledCommand, $scheduledCommand]);
    $scheduledCommandWithMutexName = $scheduledCommandCollection->withMutexName($scheduledCommand->mutexName);

    expect($scheduledCommandWithMutexName)
        ->toBe($scheduledCommand);
});

test('with mutex name refuses a mutex name of no scheduled command', function (): void {
    $scheduledCommandCollection = new ScheduledCommandCollection([
        new ScheduledCommand(fake()->slug(), fake()->unique()->sha1(), CarbonImmutable::instance(fake()->dateTime())),
    ]);

    $scheduledCommandCollection->withMutexName(fake()->unique()->sha1());
})->throws(ItemNotFoundException::class);
