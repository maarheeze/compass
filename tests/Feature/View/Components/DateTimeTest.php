<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Maarheeze\Compass\View\Components\DateTime;

test('display date time is the time in the configured display timezone and format', function (): void {
    $displayTimezone = fake()->timezone();
    $displayDateTimeFormat = fake()->randomElement(['d-m-Y H:i', 'D j M Y, H:i', 'Y-m-d H:i:s']);
    $this->setConfiguration('compass.display_timezone', $displayTimezone);
    $this->setConfiguration('compass.display_date_time_format', $displayDateTimeFormat);
    $time = CarbonImmutable::instance(fake()->dateTime());

    $dateTimeComponent = $this->app->make(DateTime::class, ['time' => $time]);

    expect($dateTimeComponent->displayDateTime)
        ->toBe($time->setTimezone($displayTimezone)->format($displayDateTimeFormat));
});
