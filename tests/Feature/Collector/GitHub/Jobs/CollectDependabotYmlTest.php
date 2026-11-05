<?php

declare(strict_types=1);

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Log\Context\Repository as Context;
use Illuminate\Support\Testing\Fakes\EventFake;
use Maarheeze\Compass\Collector\BuiltInDriver;
use Maarheeze\Compass\Collector\Events\DependabotYmlChanged;
use Maarheeze\Compass\Collector\GitHub\Jobs\CollectDependabotYml;
use Maarheeze\Compass\Models\DependabotYml;
use Maarheeze\Compass\Models\Repository;
use Symfony\Component\Yaml\Yaml;

test('handle queries the blob of the current dependabot yml oid', function (): void {
    $source = fake()->slug();
    $this->setConfiguration('compass.sources', [
        $source => [
            'driver' => BuiltInDriver::GITHUB,
            'organization' => fake()->slug(),
            'token' => fake()->sha1(),
        ],
    ]);
    $repository = Repository::factory()->create(['dependabot_yml_oid' => fake()->sha1()]);

    $http = $this->app->make(HttpFactory::class);
    $http->fake([
        '*' => HttpFactory::response([
            'data' => [
                'repository' => [
                    'object' => [
                        'text' => Yaml::dump(['version' => 2]),
                    ],
                ],
            ],
        ]),
    ]);

    $collectDependabotYml = new CollectDependabotYml($repository, $source);
    $this->app->call([$collectDependabotYml, 'handle']);

    $http->assertSent(static function (HttpRequest $request) use ($repository): bool {
        if ($request['variables']['name'] !== $repository->name) {
            return false;
        }

        return $request['variables']['oid'] === $repository->dependabot_yml_oid;
    });
});

test('handle stores the package ecosystems of the dependabot yml with its oid', function (): void {
    $source = fake()->slug();
    $this->setConfiguration('compass.sources', [
        $source => [
            'driver' => BuiltInDriver::GITHUB,
            'organization' => fake()->slug(),
            'token' => fake()->sha1(),
        ],
    ]);
    $repository = Repository::factory()->create(['dependabot_yml_oid' => fake()->sha1()]);
    $packageEcosystem = fake()->randomElement(['composer', 'docker', 'github-actions', 'npm']);
    $this->app->make(HttpFactory::class)->fake([
        '*' => HttpFactory::response([
            'data' => [
                'repository' => [
                    'object' => [
                        'text' => Yaml::dump([
                            'version' => 2,
                            'updates' => [
                                [
                                    'package-ecosystem' => $packageEcosystem,
                                    'directory' => '/',
                                ],
                            ],
                        ]),
                    ],
                ],
            ],
        ]),
    ]);

    $collectDependabotYml = new CollectDependabotYml($repository, $source);
    $this->app->call([$collectDependabotYml, 'handle']);

    $this->assertDatabaseHas(DependabotYml::class, [
        'repository_id' => $repository->id,
        'oid' => $repository->dependabot_yml_oid,
        'package_ecosystems' => json_encode([$packageEcosystem], JSON_THROW_ON_ERROR),
    ]);
});

test('handle removes the dependabot yml when the repository no longer has one', function (): void {
    $source = fake()->slug();
    $this->setConfiguration('compass.sources', [
        $source => [
            'driver' => BuiltInDriver::GITHUB,
            'organization' => fake()->slug(),
            'token' => fake()->sha1(),
        ],
    ]);
    $repository = Repository::factory()->create(['dependabot_yml_oid' => null]);
    $dependabotYml = DependabotYml::factory()->create(['repository_id' => $repository->id]);

    $collectDependabotYml = new CollectDependabotYml($repository, $source);
    $this->app->call([$collectDependabotYml, 'handle']);

    $this->assertDatabaseMissing(DependabotYml::class, ['id' => $dependabotYml->id]);
});

test('handle adds the repository to the log context', function (): void {
    $source = fake()->slug();
    $this->setConfiguration('compass.sources', [
        $source => [
            'driver' => BuiltInDriver::GITHUB,
            'organization' => fake()->slug(),
            'token' => fake()->sha1(),
        ],
    ]);
    $repository = Repository::factory()->create(['dependabot_yml_oid' => null]);

    $collectDependabotYml = new CollectDependabotYml($repository, $source);
    $this->app->call([$collectDependabotYml, 'handle']);

    expect($this->app->make(Context::class)->get('repository'))
        ->toBe($repository->name);
});

test('handle refuses a source without a Dependabot files collector', function (): void {
    $key = fake()->slug();
    $this->setConfiguration('compass.sources', [
        $key => [
            'driver' => fake()->slug(),
        ],
    ]);
    $repository = Repository::factory()->create();

    $this->expectException(InvalidArgumentException::class);

    $collectDependabotYml = new CollectDependabotYml($repository, $key);
    $this->app->call([$collectDependabotYml, 'handle']);
});

test('handle raises dependabot yml changed for the repository when its file is gone', function (): void {
    $source = fake()->slug();
    $this->setConfiguration('compass.sources', [
        $source => [
            'driver' => BuiltInDriver::GITHUB,
            'organization' => fake()->slug(),
            'token' => fake()->sha1(),
        ],
    ]);
    $eventFake = new EventFake($this->app->make(Dispatcher::class));
    $this->app->instance(Dispatcher::class, $eventFake);
    $repository = Repository::factory()->create(['dependabot_yml_oid' => null]);

    $collectDependabotYml = new CollectDependabotYml($repository, $source);
    $this->app->call([$collectDependabotYml, 'handle']);

    $eventFake->assertDispatched(DependabotYmlChanged::class, static function (DependabotYmlChanged $dependabotYmlChanged) use ($repository): bool {
        return $dependabotYmlChanged->repository->is($repository);
    });
});
