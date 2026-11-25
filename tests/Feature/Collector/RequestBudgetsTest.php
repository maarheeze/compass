<?php

declare(strict_types=1);

use Maarheeze\Compass\Collector\RequestBudgetExhausted;
use Maarheeze\Compass\Collector\RequestBudgets;
use Maarheeze\Compass\Collector\SourceConfig;

test('for source refuses a request over the requests per minute of the source', function (): void {
    $requestsPerMinute = fake()->numberBetween(1, 10);
    $sourceConfig = new SourceConfig(fake()->slug(), ['requests_per_minute' => $requestsPerMinute]);

    $requestBudgets = $this->app->make(RequestBudgets::class);
    $requestBudget = $requestBudgets->forSource($sourceConfig);

    for ($requestNumber = 1; $requestNumber <= $requestsPerMinute; $requestNumber++) {
        $requestBudget->reserveRequest();
    }

    $requestBudget->reserveRequest();
})->throws(RequestBudgetExhausted::class);

test('for source counts the requests of each source apart', function (): void {
    $requestBudgets = $this->app->make(RequestBudgets::class);
    $exhaustedRequestBudget = $requestBudgets->forSource(
        new SourceConfig(fake()->unique()->slug(), ['requests_per_minute' => 1]),
    );
    $exhaustedRequestBudget->reserveRequest();

    $requestBudget = $requestBudgets->forSource(
        new SourceConfig(fake()->unique()->slug(), ['requests_per_minute' => 1]),
    );

    expect(static function () use ($requestBudget): void {
        $requestBudget->reserveRequest();
    })
        ->not->toThrow(RequestBudgetExhausted::class);
});
