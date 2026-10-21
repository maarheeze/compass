<?php

declare(strict_types=1);

use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Request as HttpRequest;
use Maarheeze\Compass\Collector\GitHub\GitHubClient;
use Maarheeze\Compass\Collector\GitHub\GitHubQueryFailed;

test('query is posted to the github graphql api', function (): void {
    $http = $this->app->make(HttpFactory::class);
    $http->fake(['*' => HttpFactory::response([])]);

    $gitHubClient = $this->app->make(GitHubClient::class);
    $gitHubClient->query(fake()->sentence(), []);

    $http->assertSent(static function (HttpRequest $request): bool {
        if ($request->method() !== 'POST') {
            return false;
        }

        return $request->url() === 'https://api.github.com/graphql';
    });
});

test('query is sent with the ci user token', function (): void {
    $token = fake()->sha1();
    $this->setConfiguration('compass.github.token', $token);

    $http = $this->app->make(HttpFactory::class);
    $http->fake(['*' => HttpFactory::response([])]);

    $gitHubClient = $this->app->make(GitHubClient::class);
    $gitHubClient->query(fake()->sentence(), []);

    $http->assertSent(static function (HttpRequest $request) use ($token): bool {
        return $request->hasHeader('Authorization', sprintf('Bearer %s', $token));
    });
});

test('query is sent with its variables', function (): void {
    $query = fake()->sentence();
    $variables = [
        fake()->unique()->word() => fake()->word(),
        fake()->unique()->word() => null,
    ];

    $http = $this->app->make(HttpFactory::class);
    $http->fake(['*' => HttpFactory::response([])]);

    $gitHubClient = $this->app->make(GitHubClient::class);
    $gitHubClient->query($query, $variables);

    $http->assertSent(static function (HttpRequest $request) use ($query, $variables): bool {
        if ($request['query'] !== $query) {
            return false;
        }

        return $request['variables'] === $variables;
    });
});

test('query throws when github answers with errors', function (): void {
    $this->app->make(HttpFactory::class)->fake([
        '*' => HttpFactory::response([
            'errors' => [
                [
                    'message' => fake()->sentence(),
                ],
            ],
        ]),
    ]);

    $gitHubClient = $this->app->make(GitHubClient::class);
    $gitHubClient->query(fake()->sentence(), []);
})->throws(GitHubQueryFailed::class);

test('query throws when github refuses the request', function (): void {
    $this->app->make(HttpFactory::class)->fake(['*' => HttpFactory::response(null, 401)]);

    $gitHubClient = $this->app->make(GitHubClient::class);
    $gitHubClient->query(fake()->sentence(), []);
})->throws(GitHubQueryFailed::class);

test('query throws when github cannot be reached', function (): void {
    $this->app->make(HttpFactory::class)->fake(['*' => HttpFactory::failedConnection()]);

    $gitHubClient = $this->app->make(GitHubClient::class);
    $gitHubClient->query(fake()->sentence(), []);
})->throws(GitHubQueryFailed::class);
