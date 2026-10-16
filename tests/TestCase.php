<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Tests;

use Illuminate\Config\Repository;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Foundation\Application;
use Laravel\Socialite\SocialiteServiceProvider;
use Maarheeze\Compass\CompassServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

use function fake;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    /**
     * @param Application $app
     *
     * @throws BindingResolutionException
*/
    protected function defineEnvironment($app): void
    {
        $config = $app->make(Repository::class);
        $config->set('compass.github.client_id', fake()->uuid());
        $config->set('compass.github.client_secret', fake()->sha1());
        $config->set('compass.organization', fake()->slug());
    }

    /**
     * @param Application $app
     *
     * @return list<class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [SocialiteServiceProvider::class, CompassServiceProvider::class];
    }
}
