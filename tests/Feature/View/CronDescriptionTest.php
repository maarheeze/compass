<?php

declare(strict_types=1);

use Maarheeze\Compass\View\CronDescription;

test('describe describes a cron expression in words', function (): void {
    $cronDescription = $this->app->make(CronDescription::class);
    $description = $cronDescription->describe('* * * * *');

    expect($description)
        ->toBe('Every minute');
});

test('describe writes an hour below ten with a leading zero', function (): void {
    $cronDescription = $this->app->make(CronDescription::class);
    $description = $cronDescription->describe('30 7 * * *');

    expect($description)
        ->toBe('Every day at 07:30');
});

test('describe is the expression itself when it cannot be read', function (): void {
    $expression = fake()->word();

    $cronDescription = $this->app->make(CronDescription::class);
    $description = $cronDescription->describe($expression);

    expect($description)
        ->toBe($expression);
});
