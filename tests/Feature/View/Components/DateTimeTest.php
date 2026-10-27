<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Maarheeze\Compass\View\Components\DateTime;

test('display date time is the date and the time in the configured display timezone', function (): void {
    $displayTimezone = fake()->timezone();
    $this->setConfiguration('compass.display_timezone', $displayTimezone);
    $time = CarbonImmutable::instance(fake()->dateTime());

    $dateTimeComponent = $this->app->make(DateTime::class, ['time' => $time]);

    expect($dateTimeComponent->displayDateTime)
        ->toBe($time->setTimezone($displayTimezone)->toDateTimeString());
});
