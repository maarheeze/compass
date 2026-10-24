<?php

declare(strict_types=1);

use Maarheeze\Compass\Http\EndpointPath;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

test('from route key restores the path of the route key', function (): void {
    $endpointPath = new EndpointPath(sprintf('/%s?%s=%s', fake()->slug(), fake()->word(), fake()->word()));

    $restoredEndpointPath = EndpointPath::fromRouteKey($endpointPath->routeKey());

    expect($restoredEndpointPath->path)
        ->toBe($endpointPath->path);
});

test('route key holds only characters that are safe in a url', function (): void {
    $endpointPath = new EndpointPath(sprintf('/%s/%s?%s', fake()->slug(), fake()->slug(), fake()->sentence()));

    $routeKey = $endpointPath->routeKey();

    expect($routeKey)
        ->toMatch('/^[A-Za-z0-9_-]+$/');
});

test('from route key refuses a route key that is not encoded', function (): void {
    $this->expectException(NotFoundHttpException::class);

    EndpointPath::fromRouteKey('!');
});
