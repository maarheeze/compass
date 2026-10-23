<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Providers;

use Illuminate\Support\ServiceProvider;
use Maarheeze\Compass\Collector\Client\ClientKey;
use Maarheeze\Compass\Collector\Client\ClientUrlFactory;
use Maarheeze\Compass\Console\GenerateKeyCommand;

class ClientProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->commands([GenerateKeyCommand::class]);
    }

    public function register(): void
    {
        $this->app
            ->when(ClientKey::class)
            ->needs('$defaultKey')
            ->giveConfig('compass.client.key');

        $this->app
            ->when(ClientUrlFactory::class)
            ->needs('$defaultPath')
            ->giveConfig('compass.client.path');
    }
}
