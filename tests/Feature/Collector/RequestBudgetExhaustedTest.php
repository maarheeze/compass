<?php

declare(strict_types=1);

use Illuminate\Http\Client\Factory as HttpFactory;
use Maarheeze\Compass\Collector\RequestBudget;
use Maarheeze\Compass\Collector\RequestBudgetExhausted;

test('from rate limited response waits the seconds of its retry after', function (): void {
    $retryAfterSeconds = fake()->numberBetween(1, 3600);
    $http = $this->app->make(HttpFactory::class);
    $http->fake(['*' => HttpFactory::response(null, 429, ['Retry-After' => sprintf('%d', $retryAfterSeconds)])]);
    $rateLimitedResponse = $http->get(fake()->url());

    $requestBudgetExhausted = RequestBudgetExhausted::fromRateLimitedResponse($rateLimitedResponse);

    expect($requestBudgetExhausted->secondsUntilRoom)
        ->toBe($retryAfterSeconds);
});

test('from rate limited response waits a minute without a retry after', function (): void {
    $http = $this->app->make(HttpFactory::class);
    $http->fake(['*' => HttpFactory::response(null, 429)]);
    $rateLimitedResponse = $http->get(fake()->url());

    $requestBudgetExhausted = RequestBudgetExhausted::fromRateLimitedResponse($rateLimitedResponse);

    expect($requestBudgetExhausted->secondsUntilRoom)
        ->toBe(RequestBudget::WINDOW_SECONDS);
});

test('from rate limited response waits a minute for a retry after that is not a number of seconds', function (): void {
    $http = $this->app->make(HttpFactory::class);
    $http->fake([
        '*' => HttpFactory::response(null, 429, ['Retry-After' => fake()->dateTime()->format(DATE_RFC7231)]),
    ]);
    $rateLimitedResponse = $http->get(fake()->url());

    $requestBudgetExhausted = RequestBudgetExhausted::fromRateLimitedResponse($rateLimitedResponse);

    expect($requestBudgetExhausted->secondsUntilRoom)
        ->toBe(RequestBudget::WINDOW_SECONDS);
});
