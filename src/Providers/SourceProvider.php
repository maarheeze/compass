<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Providers;

use Illuminate\Support\ServiceProvider;
use Maarheeze\Compass\Collector\SourceManager;
use Maarheeze\Compass\Console\CollectCommand;

class SourceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->commands([CollectCommand::class]);
    }

    public function register(): void
    {
        $this->app->singleton(SourceManager::class);
    }
}
