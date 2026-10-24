<?php

declare(strict_types=1);

use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Collection;
use Maarheeze\Compass\Collector\SiteEndpoint\Jobs\CheckSiteEndpoints;

test('check endpoints is scheduled every fifteen minutes', function (): void {
    $schedule = $this->app->make(Schedule::class);

    $scheduledEvent = new Collection($schedule->events())->sole(static function (Event $event): bool {
        return $event->description === CheckSiteEndpoints::class;
    });

    expect($scheduledEvent->expression)
        ->toBe('*/15 * * * *');
});
