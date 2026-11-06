<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Dependabot;

use Closure;
use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Client\Factory as HttpFactory;
use Maarheeze\Compass\Collector\BuiltInDriver;
use Maarheeze\Compass\Collector\CollectorDrivers;
use Maarheeze\Compass\Collector\Drivers\GitHub\GitHubClient;
use Maarheeze\Compass\Collector\Drivers\GitHub\GitHubRepositoryFilesCollector;
use Maarheeze\Compass\Collector\SourceConfig;
use Maarheeze\Compass\Collector\SourceConfigs;

readonly class DependabotFilesCollectorManager
{
    /** @var CollectorDrivers<DependabotFilesCollector> */
    private CollectorDrivers $collectorDrivers;

    public function __construct(
        Container $container,
        private HttpFactory $http,
        private SourceConfigs $sourceConfigs,
    ) {
        $this->collectorDrivers = new CollectorDrivers($container);
        $this->collectorDrivers->extend(
            BuiltInDriver::GITHUB,
            function (Container $container, SourceConfig $sourceConfig): GitHubRepositoryFilesCollector {
                return new GitHubRepositoryFilesCollector(
                    new GitHubClient($this->http, $sourceConfig->setting('token')),
                    $sourceConfig->setting('organization'),
                );
            },
        );
    }

    public function collector(string $source): DependabotFilesCollector
    {
        return $this->collectorDrivers->collector($this->sourceConfigs->get($source));
    }

    /**
     * @param Closure(Container, SourceConfig): DependabotFilesCollector $creator
     */
    public function extend(string $driverName, Closure $creator): void
    {
        $this->collectorDrivers->extend($driverName, $creator);
    }

    public function hasCollector(string $source): bool
    {
        return $this->collectorDrivers->has($this->sourceConfigs->get($source)->driver());
    }
}
