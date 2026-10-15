<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Tests;

use Illuminate\Contracts\Foundation\Application;
use Laravel\Socialite\SocialiteServiceProvider;
use Maarheeze\Compass\CompassServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

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
     * @return list<class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [SocialiteServiceProvider::class, CompassServiceProvider::class];
    }
}
