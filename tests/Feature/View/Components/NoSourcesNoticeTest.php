<?php

declare(strict_types=1);

use Maarheeze\Compass\View\Components\NoSourcesNotice;

test('render renders the no sources notice view', function (): void {
    $noSourcesNotice = $this->app->make(NoSourcesNotice::class);

    expect($noSourcesNotice->render()->name())
        ->toBe('compass::components.no-sources-notice');
});

test('should render when no source is configured', function (): void {
    $this->setConfiguration('compass.sources', []);

    $noSourcesNotice = $this->app->make(NoSourcesNotice::class);

    expect($noSourcesNotice->shouldRender())
        ->toBeTrue();
});

test('should not render when a source is configured', function (): void {
    $this->setConfiguration('compass.sources', [
        fake()->slug() => [
            'driver' => fake()->slug(),
        ],
    ]);

    $noSourcesNotice = $this->app->make(NoSourcesNotice::class);

    expect($noSourcesNotice->shouldRender())
        ->toBeFalse();
});
