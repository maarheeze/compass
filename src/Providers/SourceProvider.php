<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Providers;

use Illuminate\Support\ServiceProvider;
use Maarheeze\Compass\Collector\SourceManager;

class SourceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SourceManager::class);
    }
}
