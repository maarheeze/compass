<?php

declare(strict_types=1);

use Maarheeze\Compass\Collector\RunStatus;
use Maarheeze\Compass\Collector\RunType;
use Maarheeze\Compass\Models\Run;

test('of type keeps a run of the type', function (): void {
    $run = Run::factory()->create();

    $runsOfType = Run::query()
        ->ofType($run->type)
        ->get();

    expect($runsOfType->sole()->is($run))
        ->toBeTrue();
});

test('of type leaves out a run of another type', function (): void {
    Run::factory()->create(['type' => fake()->unique()->randomElement(RunType::cases())]);

    $runsOfType = Run::query()
        ->ofType(fake()->unique()->randomElement(RunType::cases()))
        ->get();

    expect($runsOfType)
        ->toBeEmpty();
});

test('unfinished keeps an unfinished run', function (RunStatus $runStatus): void {
    $run = Run::factory()->create(['status' => $runStatus]);

    $unfinishedRuns = Run::query()
        ->unfinished()
        ->get();

    expect($unfinishedRuns->sole()->is($run))
        ->toBeTrue();
})->with([RunStatus::QUEUED, RunStatus::RUNNING]);

test('unfinished leaves out a finished run', function (RunStatus $runStatus): void {
    Run::factory()->create(['status' => $runStatus]);

    $unfinishedRuns = Run::query()
        ->unfinished()
        ->get();

    expect($unfinishedRuns)
        ->toBeEmpty();
})->with([RunStatus::FAILED, RunStatus::SUCCEEDED]);
