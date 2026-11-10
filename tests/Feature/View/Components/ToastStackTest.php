<?php

declare(strict_types=1);

use Maarheeze\Compass\Alerts\AlertConfirmation;
use Maarheeze\Compass\Alerts\AlertHistoryEntryType;
use Maarheeze\Compass\Collector\RunStatus;
use Maarheeze\Compass\Models\Run;
use Maarheeze\Compass\View\Components\ToastStack;

test('confirmed entry type is the history entry type of the alert confirmation', function (): void {
    $alertHistoryEntryType = fake()->randomElement(AlertHistoryEntryType::cases());
    $this->app
        ->make(AlertConfirmation::class)
        ->flash($alertHistoryEntryType);

    $toastStack = $this->app->make(ToastStack::class);

    expect($toastStack->confirmedEntryType)
        ->toBe($alertHistoryEntryType);
});

test('running runs are the runs whose status is running', function (): void {
    $runningRun = Run::factory()->create(['status' => RunStatus::RUNNING]);
    Run::factory()->create([
        'status' => fake()->randomElement([RunStatus::QUEUED, RunStatus::SUCCEEDED, RunStatus::FAILED]),
    ]);

    $toastStack = $this->app->make(ToastStack::class);

    expect($toastStack->runningRuns->sole()->is($runningRun))
        ->toBeTrue();
});
