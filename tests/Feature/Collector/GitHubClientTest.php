<?php

declare(strict_types=1);

use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Request as HttpRequest;
use Maarheeze\Compass\Collector\GitHubClient;

test('request is sent to the github api', function (): void {
    $path = fake()->slug();

    $http = $this->app->make(HttpFactory::class);
    $http->fake(['*' => HttpFactory::response([])]);

    $gitHubClient = $this->app->make(GitHubClient::class);
    $gitHubClient->request()->get($path);

    $http->assertSent(static function (HttpRequest $request) use ($path): bool {
        return $request->url() === sprintf('https://api.github.com/%s', $path);
    });
});

test('request is sent with the ci user token', function (): void {
    $token = fake()->sha1();
    $this->setConfiguration('compass.collector.github_token', $token);

    $http = $this->app->make(HttpFactory::class);
    $http->fake(['*' => HttpFactory::response([])]);

    $gitHubClient = $this->app->make(GitHubClient::class);
    $gitHubClient->request()->get(fake()->slug());

    $http->assertSent(static function (HttpRequest $request) use ($token): bool {
        return $request->hasHeader('Authorization', sprintf('Bearer %s', $token));
    });
});
