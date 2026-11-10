<?php

declare(strict_types=1);

use Maarheeze\Compass\Collector\RunStatus;
use Maarheeze\Compass\Models\Run;
use Maarheeze\Compass\View\Components\RunToast;
use Maarheeze\Compass\View\ToastType;

test('should render is false for a queued run', function (): void {
    $run = Run::factory()->create(['status' => RunStatus::QUEUED]);

    $runToast = $this->app->make(RunToast::class, ['run' => $run]);
    $shouldRender = $runToast->shouldRender();

    expect($shouldRender)
        ->toBeFalse();
});

test('should render is true for a running run', function (): void {
    $run = Run::factory()->create(['status' => RunStatus::RUNNING]);

    $runToast = $this->app->make(RunToast::class, ['run' => $run]);
    $shouldRender = $runToast->shouldRender();

    expect($shouldRender)
        ->toBeTrue();
});

test('toast type of a running run is neutral', function (): void {
    $run = Run::factory()->create(['status' => RunStatus::RUNNING]);

    $runToast = $this->app->make(RunToast::class, ['run' => $run]);

    expect($runToast->toastType())
        ->toBe(ToastType::NEUTRAL);
});

test('toast type of a succeeded run is success', function (): void {
    $run = Run::factory()->create(['status' => RunStatus::SUCCEEDED]);

    $runToast = $this->app->make(RunToast::class, ['run' => $run]);

    expect($runToast->toastType())
        ->toBe(ToastType::SUCCESS);
});

test('toast type of a failed run is danger', function (): void {
    $run = Run::factory()->create(['status' => RunStatus::FAILED]);

    $runToast = $this->app->make(RunToast::class, ['run' => $run]);

    expect($runToast->toastType())
        ->toBe(ToastType::DANGER);
});
