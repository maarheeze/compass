<?php

declare(strict_types=1);

use Illuminate\Contracts\Container\Container;
use Maarheeze\Compass\Collector\Forge\ForgeDriver;
use Maarheeze\Compass\Collector\GitHub\GitHubDriver;
use Maarheeze\Compass\Collector\Source;
use Maarheeze\Compass\Collector\SourceManager;

test('source is a github source with the organization and token of its config', function (): void {
    $key = fake()->slug();
    $organization = fake()->slug();
    $token = fake()->sha1();
    $this->setConfiguration('compass.sources', [
        $key => [
            'driver' => 'github',
            'organization' => $organization,
            'token' => $token,
        ],
    ]);

    $sourceManager = $this->app->make(SourceManager::class);
    $source = $sourceManager->source($key);

    expect($source)
        ->toEqual(new Source($key, new GitHubDriver($organization, $token)));
});

test('source is a forge source with the organization, token, server name prefix and grace minutes of its config', function (): void {
    $key = fake()->slug();
    $organization = fake()->slug();
    $token = fake()->sha1();
    $serverNamePrefix = fake()->slug();
    $autoDeployGraceMinutes = fake()->numberBetween(1, 60);
    $this->setConfiguration('compass.sources', [
        $key => [
            'driver' => 'forge',
            'organization' => $organization,
            'token' => $token,
            'server_name_prefix' => $serverNamePrefix,
            'auto_deploy_grace_minutes' => $autoDeployGraceMinutes,
        ],
    ]);

    $sourceManager = $this->app->make(SourceManager::class);
    $source = $sourceManager->source($key);

    expect($source)
        ->toEqual(new Source($key, new ForgeDriver(
            $organization,
            $token,
            $serverNamePrefix,
            $autoDeployGraceMinutes,
        )));
});

test('source reads the grace minutes of a forge source given as a string from the environment', function (): void {
    $key = fake()->slug();
    $autoDeployGraceMinutes = fake()->numberBetween(1, 60);
    $this->setConfiguration('compass.sources', [
        $key => [
            'driver' => 'forge',
            'organization' => fake()->slug(),
            'token' => fake()->sha1(),
            'server_name_prefix' => fake()->slug(),
            'auto_deploy_grace_minutes' => sprintf('%d', $autoDeployGraceMinutes),
        ],
    ]);

    $sourceManager = $this->app->make(SourceManager::class);
    $source = $sourceManager->source($key);

    expect($source->driver)
        ->toBeInstanceOf(ForgeDriver::class)
        ->autoDeployGraceMinutes->toBe($autoDeployGraceMinutes);
});

test('source has the driver its extension makes from its config', function (): void {
    $key = fake()->slug();
    $driverName = fake()->slug();
    $organization = fake()->slug();
    $token = fake()->sha1();
    $this->setConfiguration('compass.sources', [
        $key => [
            'driver' => $driverName,
            'organization' => $organization,
            'token' => $token,
        ],
    ]);

    $sourceManager = $this->app->make(SourceManager::class);
    $sourceManager->extend($driverName, static function (Container $container, array $sourceConfig): GitHubDriver {
        return new GitHubDriver($sourceConfig['organization'], $sourceConfig['token']);
    });
    $source = $sourceManager->source($key);

    expect($source->driver)
        ->toEqual(new GitHubDriver($organization, $token));
});

test('source of a shipped source has its driver', function (string $key, string $driverClass): void {
    $sourceManager = $this->app->make(SourceManager::class);

    $source = $sourceManager->source($key);

    expect($source->driver)
        ->toBeInstanceOf($driverClass);
})->with([
    'forge' => ['forge', ForgeDriver::class],
    'github' => ['github', GitHubDriver::class],
]);

test('source is the same source each time it is asked for', function (): void {
    $key = fake()->slug();
    $this->setConfiguration('compass.sources', [
        $key => [
            'driver' => 'github',
            'organization' => fake()->slug(),
            'token' => fake()->sha1(),
        ],
    ]);

    $sourceManager = $this->app->make(SourceManager::class);
    $firstSource = $sourceManager->source($key);
    $secondSource = $sourceManager->source($key);

    expect($secondSource)
        ->toBe($firstSource);
});

test('source has a driver of its own for each source', function (): void {
    $firstKey = fake()->unique()->slug();
    $secondKey = fake()->unique()->slug();
    $this->setConfiguration('compass.sources', [
        $firstKey => [
            'driver' => 'github',
            'organization' => fake()->slug(),
            'token' => fake()->sha1(),
        ],
        $secondKey => [
            'driver' => 'github',
            'organization' => fake()->slug(),
            'token' => fake()->sha1(),
        ],
    ]);

    $sourceManager = $this->app->make(SourceManager::class);
    $firstSource = $sourceManager->source($firstKey);
    $secondSource = $sourceManager->source($secondKey);

    expect($secondSource->driver)
        ->not->toBe($firstSource->driver);
});

test('source refuses a key no source is configured under', function (): void {
    $this->setConfiguration('compass.sources', []);

    $sourceManager = $this->app->make(SourceManager::class);

    $sourceManager->source(fake()->slug());
})->throws(InvalidArgumentException::class);

test('source refuses a source naming a driver that is neither built in nor extended', function (): void {
    $key = fake()->slug();
    $this->setConfiguration('compass.sources', [
        $key => [
            'driver' => fake()->slug(),
        ],
    ]);

    $sourceManager = $this->app->make(SourceManager::class);

    $sourceManager->source($key);
})->throws(InvalidArgumentException::class);

test('source makes a source while another names a driver that is neither built in nor extended', function (): void {
    $key = fake()->unique()->slug();
    $organization = fake()->slug();
    $token = fake()->sha1();
    $this->setConfiguration('compass.sources', [
        $key => [
            'driver' => 'github',
            'organization' => $organization,
            'token' => $token,
        ],
        fake()->unique()->slug() => [
            'driver' => fake()->slug(),
        ],
    ]);

    $sourceManager = $this->app->make(SourceManager::class);
    $source = $sourceManager->source($key);

    expect($source)
        ->toEqual(new Source($key, new GitHubDriver($organization, $token)));
});

test('source refuses a source with a setting its driver needs missing or wrong', function (array $sourceConfig): void {
    $key = fake()->slug();
    $this->setConfiguration('compass.sources', [$key => $sourceConfig]);

    $this->expectException(InvalidArgumentException::class);

    $sourceManager = $this->app->make(SourceManager::class);
    $sourceManager->source($key);
})->with([
    'github without an organization' => [
        function (): array {
            return [
                'driver' => 'github',
                'token' => fake()->sha1(),
            ];
        },
    ],
    'github with an unset token' => [
        function (): array {
            return [
                'driver' => 'github',
                'organization' => fake()->slug(),
                'token' => null,
            ];
        },
    ],
    'forge without a server name prefix' => [
        function (): array {
            return [
                'driver' => 'forge',
                'organization' => fake()->slug(),
                'token' => fake()->sha1(),
                'auto_deploy_grace_minutes' => fake()->numberBetween(1, 60),
            ];
        },
    ],
    'forge with grace minutes that are not a number' => [
        function (): array {
            return [
                'driver' => 'forge',
                'organization' => fake()->slug(),
                'token' => fake()->sha1(),
                'server_name_prefix' => fake()->slug(),
                'auto_deploy_grace_minutes' => fake()->word(),
            ];
        },
    ],
]);
