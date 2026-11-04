<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Providers;

use Illuminate\Support\ServiceProvider;
use Maarheeze\Compass\Console\GenerateKeyCommand;

class ClientProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->commands([GenerateKeyCommand::class]);
    }
}
