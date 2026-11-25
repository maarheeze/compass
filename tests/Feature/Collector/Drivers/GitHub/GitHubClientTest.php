<?php

declare(strict_types=1);

use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Request as HttpRequest;
use Maarheeze\Compass\Collector\Drivers\GitHub\GitHubAnsweredWithError;
use Maarheeze\Compass\Collector\Drivers\GitHub\GitHubClient;
use Maarheeze\Compass\Collector\Drivers\GitHub\GitHubQueryFailed;
use Maarheeze\Compass\Collector\RequestBudget;
use Maarheeze\Compass\Collector\RequestBudgetExhausted;

test('query is posted to the github graphql api', function (): void {
    $http = $this->app->make(HttpFactory::class);
    $http->fake(['*' => HttpFactory::response([])]);

    $gitHubClient = new GitHubClient(
        $http,
        fake()->sha1(),
        new RequestBudget($this->app->make(RateLimiter::class), fake()->slug(), fake()->numberBetween(10, 100)),
    );
    $gitHubClient->query(fake()->sentence(), []);

    $http->assertSent(static function (HttpRequest $request): bool {
        if ($request->method() !== 'POST') {
            return false;
        }

        return $request->url() === 'https://api.github.com/graphql';
    });
});

test('query is sent with its token', function (): void {
    $token = fake()->sha1();

    $http = $this->app->make(HttpFactory::class);
    $http->fake(['*' => HttpFactory::response([])]);

    $gitHubClient = new GitHubClient(
        $http,
        $token,
        new RequestBudget($this->app->make(RateLimiter::class), fake()->slug(), fake()->numberBetween(10, 100)),
    );
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

    $gitHubClient = new GitHubClient(
        $http,
        fake()->sha1(),
        new RequestBudget($this->app->make(RateLimiter::class), fake()->slug(), fake()->numberBetween(10, 100)),
    );
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

    $gitHubClient = new GitHubClient(
        $this->app->make(HttpFactory::class),
        fake()->sha1(),
        new RequestBudget($this->app->make(RateLimiter::class), fake()->slug(), fake()->numberBetween(10, 100)),
    );
    $gitHubClient->query(fake()->sentence(), []);
})->throws(GitHubQueryFailed::class);

test('query throws with the errors github answered with behind it, in their order', function (): void {
    $firstErrorMessage = fake()->unique()->sentence();
    $secondErrorMessage = fake()->unique()->sentence();
    $this->app->make(HttpFactory::class)->fake([
        '*' => HttpFactory::response([
            'errors' => [
                [
                    'message' => $firstErrorMessage,
                ],
                [
                    'message' => $secondErrorMessage,
                ],
            ],
        ]),
    ]);

    $gitHubClient = new GitHubClient(
        $this->app->make(HttpFactory::class),
        fake()->sha1(),
        new RequestBudget($this->app->make(RateLimiter::class), fake()->slug(), fake()->numberBetween(10, 100)),
    );

    expect(static function () use ($gitHubClient): void {
        $gitHubClient->query(fake()->sentence(), []);
    })->toThrow(static function (GitHubQueryFailed $gitHubQueryFailed) use ($firstErrorMessage, $secondErrorMessage): void {
        $firstGitHubAnsweredWithError = $gitHubQueryFailed->getPrevious();

        expect($firstGitHubAnsweredWithError)
            ->toBeInstanceOf(GitHubAnsweredWithError::class)
            ->and($firstGitHubAnsweredWithError->getMessage())
            ->toBe($firstErrorMessage)
            ->and($firstGitHubAnsweredWithError->getPrevious()?->getMessage())
            ->toBe($secondErrorMessage);
    });
});

test('query throws when github refuses the request', function (): void {
    $this->app->make(HttpFactory::class)->fake(['*' => HttpFactory::response(null, 401)]);

    $gitHubClient = new GitHubClient(
        $this->app->make(HttpFactory::class),
        fake()->sha1(),
        new RequestBudget($this->app->make(RateLimiter::class), fake()->slug(), fake()->numberBetween(10, 100)),
    );
    $gitHubClient->query(fake()->sentence(), []);
})->throws(GitHubQueryFailed::class);

test('query throws when github cannot be reached', function (): void {
    $this->app->make(HttpFactory::class)->fake(['*' => HttpFactory::failedConnection()]);

    $gitHubClient = new GitHubClient(
        $this->app->make(HttpFactory::class),
        fake()->sha1(),
        new RequestBudget($this->app->make(RateLimiter::class), fake()->slug(), fake()->numberBetween(10, 100)),
    );
    $gitHubClient->query(fake()->sentence(), []);
})->throws(GitHubQueryFailed::class);

test('get is sent to the github api', function (): void {
    $path = fake()->slug();

    $http = $this->app->make(HttpFactory::class);
    $http->fake(['*' => HttpFactory::response([])]);

    $gitHubClient = new GitHubClient(
        $http,
        fake()->sha1(),
        new RequestBudget($this->app->make(RateLimiter::class), fake()->slug(), fake()->numberBetween(10, 100)),
    );
    $gitHubClient->get($path);

    $http->assertSent(static function (HttpRequest $request) use ($path): bool {
        if ($request->method() !== 'GET') {
            return false;
        }

        return $request->url() === sprintf('https://api.github.com/%s', $path);
    });
});

test('get is sent to an absolute url as is', function (): void {
    $url = fake()->url();

    $http = $this->app->make(HttpFactory::class);
    $http->fake(['*' => HttpFactory::response([])]);

    $gitHubClient = new GitHubClient(
        $http,
        fake()->sha1(),
        new RequestBudget($this->app->make(RateLimiter::class), fake()->slug(), fake()->numberBetween(10, 100)),
    );
    $gitHubClient->get($url);

    $http->assertSent(static function (HttpRequest $request) use ($url): bool {
        return $request->url() === $url;
    });
});

test('get is sent with its token', function (): void {
    $token = fake()->sha1();

    $http = $this->app->make(HttpFactory::class);
    $http->fake(['*' => HttpFactory::response([])]);

    $gitHubClient = new GitHubClient(
        $http,
        $token,
        new RequestBudget($this->app->make(RateLimiter::class), fake()->slug(), fake()->numberBetween(10, 100)),
    );
    $gitHubClient->get(fake()->slug());

    $http->assertSent(static function (HttpRequest $request) use ($token): bool {
        return $request->hasHeader('Authorization', sprintf('Bearer %s', $token));
    });
});

test('get throws when github refuses the request', function (): void {
    $this->app->make(HttpFactory::class)->fake(['*' => HttpFactory::response(null, 401)]);

    $gitHubClient = new GitHubClient(
        $this->app->make(HttpFactory::class),
        fake()->sha1(),
        new RequestBudget($this->app->make(RateLimiter::class), fake()->slug(), fake()->numberBetween(10, 100)),
    );
    $gitHubClient->get(fake()->slug());
})->throws(GitHubQueryFailed::class);

test('get throws when github cannot be reached', function (): void {
    $this->app->make(HttpFactory::class)->fake(['*' => HttpFactory::failedConnection()]);

    $gitHubClient = new GitHubClient(
        $this->app->make(HttpFactory::class),
        fake()->sha1(),
        new RequestBudget($this->app->make(RateLimiter::class), fake()->slug(), fake()->numberBetween(10, 100)),
    );
    $gitHubClient->get(fake()->slug());
})->throws(GitHubQueryFailed::class);

test('get sends nothing while the request budget is exhausted', function (): void {
    $http = $this->app->make(HttpFactory::class);
    $http->fake(['*' => HttpFactory::response([])]);
    $requestBudget = new RequestBudget($this->app->make(RateLimiter::class), fake()->slug(), 1);
    $requestBudget->reserveRequest();

    $gitHubClient = new GitHubClient($http, fake()->sha1(), $requestBudget);

    expect(static function () use ($gitHubClient): void {
        $gitHubClient->get(fake()->slug());
    })
        ->toThrow(RequestBudgetExhausted::class);
    $http->assertNothingSent();
});

test('get waits when github answers that its rate limit is reached', function (int $status, array $headers): void {
    $this->app->make(HttpFactory::class)->fake(['*' => HttpFactory::response(null, $status, $headers)]);

    $gitHubClient = new GitHubClient(
        $this->app->make(HttpFactory::class),
        fake()->sha1(),
        new RequestBudget($this->app->make(RateLimiter::class), fake()->slug(), fake()->numberBetween(10, 100)),
    );
    $gitHubClient->get(fake()->slug());
})->throws(RequestBudgetExhausted::class)->with([
    'too many requests' => [429, []],
    'forbidden with no requests remaining' => [403, ['X-RateLimit-Remaining' => '0']],
    'forbidden with a retry after' => [403, ['Retry-After' => '60']],
]);

test('get throws when github forbids the request without a rate limit reached', function (): void {
    $this->app->make(HttpFactory::class)->fake([
        '*' => HttpFactory::response(null, 403, ['X-RateLimit-Remaining' => fake()->numerify('1##')]),
    ]);

    $gitHubClient = new GitHubClient(
        $this->app->make(HttpFactory::class),
        fake()->sha1(),
        new RequestBudget($this->app->make(RateLimiter::class), fake()->slug(), fake()->numberBetween(10, 100)),
    );
    $gitHubClient->get(fake()->slug());
})->throws(GitHubQueryFailed::class);

test('query sends nothing while the request budget is exhausted', function (): void {
    $http = $this->app->make(HttpFactory::class);
    $http->fake(['*' => HttpFactory::response([])]);
    $requestBudget = new RequestBudget($this->app->make(RateLimiter::class), fake()->slug(), 1);
    $requestBudget->reserveRequest();

    $gitHubClient = new GitHubClient($http, fake()->sha1(), $requestBudget);

    expect(static function () use ($gitHubClient): void {
        $gitHubClient->query(fake()->sentence(), []);
    })
        ->toThrow(RequestBudgetExhausted::class);
    $http->assertNothingSent();
});

test('query waits when github answers with too many requests', function (): void {
    $this->app->make(HttpFactory::class)->fake(['*' => HttpFactory::response(null, 429)]);

    $gitHubClient = new GitHubClient(
        $this->app->make(HttpFactory::class),
        fake()->sha1(),
        new RequestBudget($this->app->make(RateLimiter::class), fake()->slug(), fake()->numberBetween(10, 100)),
    );
    $gitHubClient->query(fake()->sentence(), []);
})->throws(RequestBudgetExhausted::class);
