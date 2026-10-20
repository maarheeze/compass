<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Maarheeze\Compass\View\Components\Time;

test('display time is the time relative to now', function (): void {
    $time = CarbonImmutable::instance(fake()->dateTime());

    $timeComponent = $this->app->make(Time::class, ['time' => $time]);

    expect($timeComponent->displayTime)
        ->toBe($time->diffForHumans());
});
