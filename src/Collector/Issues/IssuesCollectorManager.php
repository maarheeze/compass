<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Issues;

use Closure;
use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Client\Factory as HttpFactory;
use Maarheeze\Compass\Collector\CollectorDrivers;
use Maarheeze\Compass\Collector\Drivers\GitHub\GitHubClient;
use Maarheeze\Compass\Collector\Drivers\GitHub\GitHubIssuesCollector;
use Maarheeze\Compass\Collector\SourceConfig;
use Maarheeze\Compass\Collector\SourceConfigs;

readonly class IssuesCollectorManager
{
    /** @var CollectorDrivers<IssuesCollector> */
    private CollectorDrivers $collectorDrivers;

    public function __construct(
        Container $container,
        private HttpFactory $http,
        private SourceConfigs $sourceConfigs,
    ) {
        $this->collectorDrivers = new CollectorDrivers($container);
        $this->collectorDrivers->extend(
            'github',
            function (Container $container, SourceConfig $sourceConfig): GitHubIssuesCollector {
                return new GitHubIssuesCollector(
                    new GitHubClient($this->http, $sourceConfig->setting('token')),
                    $sourceConfig->setting('organization'),
                );
            },
        );
    }

    public function collector(string $source): IssuesCollector
    {
        return $this->collectorDrivers->collector($this->sourceConfigs->get($source));
    }

    /**
     * @param Closure(Container, SourceConfig): IssuesCollector $creator
     */
    public function extend(string $driverName, Closure $creator): void
    {
        $this->collectorDrivers->extend($driverName, $creator);
    }
}
