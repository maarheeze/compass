<?php

declare(strict_types=1);

use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Request as HttpRequest;
use Maarheeze\Compass\Collector\GitHub\GitHubClientFactory;

test('make builds a client that is sent with the token', function (): void {
    $token = fake()->sha1();

    $http = $this->app->make(HttpFactory::class);
    $http->fake(['*' => HttpFactory::response([])]);

    $gitHubClientFactory = $this->app->make(GitHubClientFactory::class);
    $gitHubClientFactory->make($token)->query(fake()->sentence(), []);

    $http->assertSent(static function (HttpRequest $request) use ($token): bool {
        return $request->hasHeader('Authorization', sprintf('Bearer %s', $token));
    });
});
