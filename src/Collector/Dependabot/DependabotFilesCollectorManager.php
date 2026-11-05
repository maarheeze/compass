<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Dependabot;

use Closure;
use Illuminate\Contracts\Container\Container;
use Maarheeze\Compass\Collector\BuiltInDriver;
use Maarheeze\Compass\Collector\CollectorDrivers;
use Maarheeze\Compass\Collector\GitHub\GitHubClientFactory;
use Maarheeze\Compass\Collector\GitHub\GitHubDependabotFilesCollector;
use Maarheeze\Compass\Collector\SourceConfig;
use Maarheeze\Compass\Collector\SourceConfigs;

readonly class DependabotFilesCollectorManager
{
    /** @var CollectorDrivers<DependabotFilesCollector> */
    private CollectorDrivers $collectorDrivers;

    public function __construct(
        Container $container,
        private GitHubClientFactory $gitHubClientFactory,
        private SourceConfigs $sourceConfigs,
    ) {
        $this->collectorDrivers = new CollectorDrivers($container);
        $this->collectorDrivers->extend(
            BuiltInDriver::GITHUB,
            function (Container $container, SourceConfig $sourceConfig): GitHubDependabotFilesCollector {
                return $this->createGitHubDriver($sourceConfig);
            },
        );
    }

    public function collector(string $sourceKey): DependabotFilesCollector
    {
        return $this->collectorDrivers->collector($this->sourceConfigs->get($sourceKey));
    }

    /**
     * @param Closure(Container, SourceConfig): DependabotFilesCollector $creator
     */
    public function extend(string $driverName, Closure $creator): void
    {
        $this->collectorDrivers->extend($driverName, $creator);
    }

    private function createGitHubDriver(SourceConfig $sourceConfig): GitHubDependabotFilesCollector
    {
        return new GitHubDependabotFilesCollector(
            $this->gitHubClientFactory->make($sourceConfig->setting('token')),
            $sourceConfig->setting('organization'),
        );
    }
}
