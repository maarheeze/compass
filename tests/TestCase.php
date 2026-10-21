<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Tests;

use Illuminate\Config\Repository;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Client\Factory as HttpFactory;
use Laravel\Socialite\SocialiteServiceProvider;
use Maarheeze\Compass\CompassServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

use function fake;

abstract class TestCase extends Orchestra
{
    public function setConfiguration(string $key, mixed $value): void
    {
        $configuration = $this->app->make(Repository::class);
        $this->assertTrue($configuration->has($key));

        $configuration->set($key, $value);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->app->make(HttpFactory::class)->preventStrayRequests();
    }

    /**
     * @param Application $app
     *
     * @throws BindingResolutionException
*/
    protected function defineEnvironment($app): void
    {
        $configuration = $app->make(Repository::class);
        $configuration->set('compass.authentication.github.client_id', fake()->uuid());
        $configuration->set('compass.authentication.github.client_secret', fake()->sha1());
        $configuration->set('compass.collector.forge_token', fake()->sha1());
        $configuration->set('compass.collector.github_token', fake()->sha1());
        $configuration->set('compass.forge_organization', fake()->slug());
        $configuration->set('compass.github_organization', fake()->slug());
    }

    /**
     * @param Application $app
     *
     * @return list<class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            CompassServiceProvider::class,
            SocialiteServiceProvider::class,
        ];
    }
}
