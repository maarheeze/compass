<?php

declare(strict_types=1);

use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Request as HttpRequest;
use Maarheeze\Compass\Collector\Drivers\Forge\ForgeClient;
use Maarheeze\Compass\Collector\Drivers\Forge\ForgeRequestFailed;
use Maarheeze\Compass\Collector\RequestBudget;
use Maarheeze\Compass\Collector\RequestBudgetExhausted;

test('get is sent to the forge api at its path', function (): void {
    $path = fake()->slug();

    $http = $this->app->make(HttpFactory::class);
    $http->fake(['*' => HttpFactory::response([])]);

    $forgeClient = new ForgeClient(
        $http,
        fake()->sha1(),
        new RequestBudget($this->app->make(RateLimiter::class), fake()->slug(), fake()->numberBetween(10, 100)),
    );
    $forgeClient->get($path, []);

    $http->assertSent(static function (HttpRequest $request) use ($path): bool {
        if ($request->method() !== 'GET') {
            return false;
        }

        return $request->url() === sprintf('https://forge.laravel.com/api/%s', $path);
    });
});

test('get is sent with the forge token', function (): void {
    $token = fake()->sha1();

    $http = $this->app->make(HttpFactory::class);
    $http->fake(['*' => HttpFactory::response([])]);

    $forgeClient = new ForgeClient(
        $http,
        $token,
        new RequestBudget($this->app->make(RateLimiter::class), fake()->slug(), fake()->numberBetween(10, 100)),
    );
    $forgeClient->get(fake()->slug(), []);

    $http->assertSent(static function (HttpRequest $request) use ($token): bool {
        return $request->hasHeader('Authorization', sprintf('Bearer %s', $token));
    });
});

test('get is sent with its query', function (): void {
    $query = [fake()->word() => fake()->word()];

    $http = $this->app->make(HttpFactory::class);
    $http->fake(['*' => HttpFactory::response([])]);

    $forgeClient = new ForgeClient(
        $http,
        fake()->sha1(),
        new RequestBudget($this->app->make(RateLimiter::class), fake()->slug(), fake()->numberBetween(10, 100)),
    );
    $forgeClient->get(fake()->slug(), $query);

    $http->assertSent(static function (HttpRequest $request) use ($query): bool {
        return $request->data() === $query;
    });
});

test('get throws when forge refuses the request', function (): void {
    $this->app->make(HttpFactory::class)->fake(['*' => HttpFactory::response(null, 401)]);

    $forgeClient = new ForgeClient(
        $this->app->make(HttpFactory::class),
        fake()->sha1(),
        new RequestBudget($this->app->make(RateLimiter::class), fake()->slug(), fake()->numberBetween(10, 100)),
    );
    $forgeClient->get(fake()->slug(), []);
})->throws(ForgeRequestFailed::class);

test('get throws when forge cannot be reached', function (): void {
    $this->app->make(HttpFactory::class)->fake(['*' => HttpFactory::failedConnection()]);

    $forgeClient = new ForgeClient(
        $this->app->make(HttpFactory::class),
        fake()->sha1(),
        new RequestBudget($this->app->make(RateLimiter::class), fake()->slug(), fake()->numberBetween(10, 100)),
    );
    $forgeClient->get(fake()->slug(), []);
})->throws(ForgeRequestFailed::class);

test('get sends nothing while the request budget is exhausted', function (): void {
    $http = $this->app->make(HttpFactory::class);
    $http->fake(['*' => HttpFactory::response([])]);
    $requestBudget = new RequestBudget($this->app->make(RateLimiter::class), fake()->slug(), 1);
    $requestBudget->reserveRequest();

    $forgeClient = new ForgeClient($http, fake()->sha1(), $requestBudget);

    expect(static function () use ($forgeClient): void {
        $forgeClient->get(fake()->slug(), []);
    })
        ->toThrow(RequestBudgetExhausted::class);
    $http->assertNothingSent();
});

test('get waits when forge answers with too many requests', function (): void {
    $this->app->make(HttpFactory::class)->fake(['*' => HttpFactory::response(null, 429)]);

    $forgeClient = new ForgeClient(
        $this->app->make(HttpFactory::class),
        fake()->sha1(),
        new RequestBudget($this->app->make(RateLimiter::class), fake()->slug(), fake()->numberBetween(10, 100)),
    );
    $forgeClient->get(fake()->slug(), []);
})->throws(RequestBudgetExhausted::class);
