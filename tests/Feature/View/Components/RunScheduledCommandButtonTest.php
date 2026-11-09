<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Maarheeze\Compass\Console\ScheduledCommand;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\View\Components\RunScheduledCommandButton;

test('run url is the run route', function (): void {
    $scheduledCommand = new ScheduledCommand(
        fake()->slug(),
        fake()->sha1(),
        CarbonImmutable::instance(fake()->dateTime()),
    );

    $runScheduledCommandButton = $this->app->make(RunScheduledCommandButton::class, [
        'scheduledCommand' => $scheduledCommand,
    ]);

    expect($runScheduledCommandButton->runUrl)
        ->toBe(route(RouteName::RUNS_RUN));
});
