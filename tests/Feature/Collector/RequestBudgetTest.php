<?php

declare(strict_types=1);

use Illuminate\Cache\RateLimiter;
use Maarheeze\Compass\Collector\RequestBudget;
use Maarheeze\Compass\Collector\RequestBudgetExhausted;

test('reserve request lets the requests of a minute through up to the requests per minute', function (): void {
    $requestsPerMinute = fake()->numberBetween(1, 10);

    $requestBudget = new RequestBudget($this->app->make(RateLimiter::class), fake()->slug(), $requestsPerMinute);

    expect(static function () use ($requestBudget, $requestsPerMinute): void {
        for ($requestNumber = 1; $requestNumber <= $requestsPerMinute; $requestNumber++) {
            $requestBudget->reserveRequest();
        }
    })
        ->not->toThrow(RequestBudgetExhausted::class);
});

test('reserve request refuses a request over the requests per minute', function (): void {
    $requestsPerMinute = fake()->numberBetween(1, 10);
    $requestBudget = new RequestBudget($this->app->make(RateLimiter::class), fake()->slug(), $requestsPerMinute);

    for ($requestNumber = 1; $requestNumber <= $requestsPerMinute; $requestNumber++) {
        $requestBudget->reserveRequest();
    }

    $requestBudget->reserveRequest();
})->throws(RequestBudgetExhausted::class);

test('reserve request refuses with the seconds left in the minute', function (): void {
    $requestBudget = new RequestBudget($this->app->make(RateLimiter::class), fake()->slug(), 1);
    $requestBudget->reserveRequest();

    expect(static function () use ($requestBudget): void {
        $requestBudget->reserveRequest();
    })->toThrow(static function (RequestBudgetExhausted $requestBudgetExhausted): void {
        expect($requestBudgetExhausted->secondsUntilRoom)
            ->toBeGreaterThan(0)
            ->toBeLessThanOrEqual(RequestBudget::WINDOW_SECONDS);
    });
});

test('reserve request counts the requests of each source apart', function (): void {
    $rateLimiter = $this->app->make(RateLimiter::class);
    $exhaustedRequestBudget = new RequestBudget($rateLimiter, fake()->unique()->slug(), 1);
    $exhaustedRequestBudget->reserveRequest();

    $requestBudget = new RequestBudget($rateLimiter, fake()->unique()->slug(), 1);

    expect(static function () use ($requestBudget): void {
        $requestBudget->reserveRequest();
    })
        ->not->toThrow(RequestBudgetExhausted::class);
});
