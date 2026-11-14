<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Maarheeze\Compass\View\Components\RelativeTime;
use Psr\Clock\ClockInterface;
use Symfony\Component\Clock\MockClock;

test('render renders the relative time view', function (): void {
    $time = CarbonImmutable::instance(fake()->dateTime());

    $relativeTime = $this->app->make(RelativeTime::class, ['time' => $time]);

    expect($relativeTime->render()->name())
        ->toBe('compass::components.relative-time');
});

test('iso date time is the time in iso 8601', function (): void {
    $time = CarbonImmutable::instance(fake()->dateTime());

    $relativeTime = $this->app->make(RelativeTime::class, ['time' => $time]);

    expect($relativeTime->isoDateTime)
        ->toBe($time->toIso8601String());
});

test('relative time is how long before now the time was', function (): void {
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $daysAgo = fake()->numberBetween(2, 6);

    $relativeTime = $this->app->make(RelativeTime::class, ['time' => $now->subDays($daysAgo)]);

    expect($relativeTime->relativeTime)
        ->toBe(sprintf('%d days ago', $daysAgo));
});
