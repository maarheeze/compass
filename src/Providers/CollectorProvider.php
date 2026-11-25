<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Providers;

use Closure;
use Illuminate\Config\Repository as ConfigurationRepository;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\ServiceProvider;
use Maarheeze\Compass\Collector\CollectorSchedule;
use Maarheeze\Compass\Collector\Drivers\CompassClient\CompassClientDriver;
use Maarheeze\Compass\Collector\Drivers\CompassClient\SiteClient;
use Maarheeze\Compass\Collector\Drivers\Forge\ForgeClient;
use Maarheeze\Compass\Collector\Drivers\Forge\ForgeDriver;
use Maarheeze\Compass\Collector\Drivers\GitHub\GitHubClient;
use Maarheeze\Compass\Collector\Drivers\GitHub\GitHubDriver;
use Maarheeze\Compass\Collector\Issues\IssueTypeMapping;
use Maarheeze\Compass\Collector\JobLock;
use Maarheeze\Compass\Collector\RequestBudgets;
use Maarheeze\Compass\Collector\SourceConfig;
use Maarheeze\Compass\Collector\SourceConfigs;
use Maarheeze\Compass\Collector\SourceDrivers;
use Maarheeze\Compass\Console\CheckSiteEndpointsCommand;
use Maarheeze\Compass\Console\CollectCommand;
use Random\Randomizer;

class CollectorProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->commands([CheckSiteEndpointsCommand::class, CollectCommand::class]);

        $this->callAfterResolving(SourceDrivers::class, function (SourceDrivers $sourceDrivers): void {
            $sourceDrivers->extend('compass-client', $this->compassClientDriverCreator());
            $sourceDrivers->extend('forge', $this->forgeDriverCreator());
            $sourceDrivers->extend('github', $this->gitHubDriverCreator());
        });
    }

    public function register(): void
    {
        $this->app->singleton(SourceDrivers::class);

        $this->app
            ->when(JobLock::class)
            ->needs('$lockExpiryMinutes')
            ->give(static function (Container $container): int {
                return $container->make(ConfigurationRepository::class)->integer('compass.lock_expiry_minutes');
            });
        $this->app
            ->when(JobLock::class)
            ->needs('$lockRetrySeconds')
            ->give(static function (Container $container): int {
                return $container->make(ConfigurationRepository::class)->integer('compass.lock_retry_seconds');
            });

        $this->app
            ->when(SourceConfigs::class)
            ->needs('$sources')
            ->giveConfig('compass.sources');
    }

    /**
     * @return Closure(Container, SourceConfig): CompassClientDriver
     */
    private function compassClientDriverCreator(): Closure
    {
        return static function (Container $container, SourceConfig $sourceConfig): CompassClientDriver {
            return new CompassClientDriver(
                $container->make(Randomizer::class),
                new SiteClient(
                    $container->make(CollectorSchedule::class),
                    $container->make(HttpFactory::class),
                    $sourceConfig->setting('path'),
                    $sourceConfig->key,
                    $container->make(UrlGenerator::class),
                ),
            );
        };
    }

    /**
     * @return Closure(Container, SourceConfig): ForgeDriver
     */
    private function forgeDriverCreator(): Closure
    {
        return static function (Container $container, SourceConfig $sourceConfig): ForgeDriver {
            return new ForgeDriver(
                new ForgeClient(
                    $container->make(HttpFactory::class),
                    $sourceConfig->setting('token'),
                    $container->make(RequestBudgets::class)->forSource($sourceConfig),
                ),
                $sourceConfig->setting('organisation'),
                $sourceConfig->setting('server_pattern'),
            );
        };
    }

    /**
     * @return Closure(Container, SourceConfig): GitHubDriver
     */
    private function gitHubDriverCreator(): Closure
    {
        return static function (Container $container, SourceConfig $sourceConfig): GitHubDriver {
            return new GitHubDriver(
                new GitHubClient(
                    $container->make(HttpFactory::class),
                    $sourceConfig->setting('token'),
                    $container->make(RequestBudgets::class)->forSource($sourceConfig),
                ),
                $sourceConfig->setting('organisation'),
                new IssueTypeMapping($sourceConfig),
            );
        };
    }
}
