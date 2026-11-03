<?php

declare(strict_types=1);

use Maarheeze\Compass\Collector\RunStatus;
use Maarheeze\Compass\Models\Run;

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
