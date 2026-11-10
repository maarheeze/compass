<?php

declare(strict_types=1);

use Maarheeze\Compass\Collector\CollectorType;
use Maarheeze\Compass\Collector\TaskType;
use Maarheeze\Compass\Models\Run;
use Maarheeze\Compass\View\Components\RunLabel;

test('label of a collector run is its source and collector', function (): void {
    $run = Run::factory()->create(['collector' => CollectorType::REPOSITORIES]);

    $runLabel = $this->app->make(RunLabel::class, ['run' => $run]);

    expect($runLabel->label)
        ->toBe(sprintf('%s · Repositories', $run->source));
});

test('label of a task run is its task', function (): void {
    $run = Run::factory()->create([
        'source' => null,
        'collector' => null,
        'task' => TaskType::SITE_ENDPOINTS,
    ]);

    $runLabel = $this->app->make(RunLabel::class, ['run' => $run]);

    expect($runLabel->label)
        ->toBe('Site endpoints');
});
