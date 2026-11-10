<?php

declare(strict_types=1);

use Maarheeze\Compass\Collector\RunStatus;
use Maarheeze\Compass\Models\Run;
use Maarheeze\Compass\View\ComponentHtml;
use Maarheeze\Compass\View\Components\RunToast;

test('html is the rendered component', function (): void {
    $run = Run::factory()->create(['status' => RunStatus::RUNNING]);
    $runToast = $this->app->make(RunToast::class, ['run' => $run]);

    $componentHtml = $this->app->make(ComponentHtml::class);
    $html = $componentHtml->html($runToast);

    expect($html)
        ->toContain(sprintf('data-compass-toast-id="%s"', $run->id));
});

test('html is null for a component that does not render', function (): void {
    $run = Run::factory()->create(['status' => RunStatus::QUEUED]);
    $runToast = $this->app->make(RunToast::class, ['run' => $run]);

    $componentHtml = $this->app->make(ComponentHtml::class);
    $html = $componentHtml->html($runToast);

    expect($html)
        ->toBeNull();
});
