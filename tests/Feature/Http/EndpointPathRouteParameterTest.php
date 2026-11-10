<?php

declare(strict_types=1);

use Maarheeze\Compass\Collector\SiteEndpoint\EndpointPath;
use Maarheeze\Compass\Http\EndpointPathRouteParameter;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

test('decode restores the endpoint path of an encoded route parameter', function (): void {
    $endpointPath = new EndpointPath(sprintf('/%s?%s=%s', fake()->slug(), fake()->word(), fake()->word()));

    $endpointPathRouteParameter = $this->app->make(EndpointPathRouteParameter::class);
    $decodedEndpointPath = $endpointPathRouteParameter->decode($endpointPathRouteParameter->encode($endpointPath));

    expect($decodedEndpointPath)
        ->toEqual($endpointPath);
});

test('encode holds only characters that are safe in a url', function (): void {
    $endpointPath = new EndpointPath(sprintf('/%s/%s?%s', fake()->slug(), fake()->slug(), fake()->sentence()));

    $endpointPathRouteParameter = $this->app->make(EndpointPathRouteParameter::class);
    $routeParameter = $endpointPathRouteParameter->encode($endpointPath);

    expect($routeParameter)
        ->toMatch('/^[A-Za-z0-9_-]+$/');
});

test('decode refuses a route parameter that is not encoded', function (): void {
    $this->expectException(NotFoundHttpException::class);

    $endpointPathRouteParameter = $this->app->make(EndpointPathRouteParameter::class);
    $endpointPathRouteParameter->decode('!');
});
