<?php

declare(strict_types=1);

use Maarheeze\Compass\Collector\RunStatus;
use Maarheeze\Compass\Collector\RunType;
use Maarheeze\Compass\Collector\SyncStatus;
use Maarheeze\Compass\Models\Run;

test('is running when a run is unfinished', function (RunStatus $runStatus): void {
    Run::factory()->create([
        'type' => fake()->randomElement(RunType::cases()),
        'status' => $runStatus,
    ]);

    $syncStatus = $this->app->make(SyncStatus::class);

    expect($syncStatus->isRunning())
        ->toBeTrue();
})->with([RunStatus::QUEUED, RunStatus::RUNNING]);

test('is not running when the runs have finished', function (): void {
    Run::factory()->create([
        'type' => fake()->randomElement(RunType::cases()),
        'status' => fake()->randomElement([RunStatus::SUCCEEDED, RunStatus::FAILED]),
    ]);

    $syncStatus = $this->app->make(SyncStatus::class);

    expect($syncStatus->isRunning())
        ->toBeFalse();
});
