<?php

declare(strict_types=1);

use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Http\Response;
use Illuminate\Support\Testing\Fakes\BusFake;
use Maarheeze\Compass\Collector\Runs\InspectRequestingSite;
use Maarheeze\Compass\Http\RouteName;

test('store queues the inspection of the site with the bearer token as its key', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);
    $key = fake()->sha1();

    $this->withToken($key)->post(route(RouteName::API_CLIENT));

    $busFake->assertDispatched(InspectRequestingSite::class, static function (InspectRequestingSite $inspectRequestingSite) use ($key): bool {
        return $inspectRequestingSite->key === $key;
    });
});

test('store queues nothing without a bearer token', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);

    $this->post(route(RouteName::API_CLIENT));

    $busFake->assertNothingDispatched();
});

test('store answers accepted', function (): void {
    $this->app->instance(QueueingDispatcher::class, new BusFake($this->app->make(QueueingDispatcher::class)));

    $response = $this->withToken(fake()->sha1())->post(route(RouteName::API_CLIENT));

    $response->assertStatus(Response::HTTP_ACCEPTED);
});

test('store refuses a request beyond the configured number per minute', function (): void {
    $inspectionRequestsPerMinute = fake()->numberBetween(1, 5);
    $this->setConfiguration('compass.inspection_requests_per_minute', $inspectionRequestsPerMinute);
    $this->app->instance(QueueingDispatcher::class, new BusFake($this->app->make(QueueingDispatcher::class)));

    for ($sentRequestCount = 0; $sentRequestCount < $inspectionRequestsPerMinute; $sentRequestCount++) {
        $this->withToken(fake()->sha1())->post(route(RouteName::API_CLIENT));
    }

    $response = $this->withToken(fake()->sha1())->post(route(RouteName::API_CLIENT));

    $response->assertStatus(Response::HTTP_TOO_MANY_REQUESTS);
});
