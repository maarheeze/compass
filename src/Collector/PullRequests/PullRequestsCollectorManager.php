<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\PullRequests;

use Closure;
use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Client\Factory as HttpFactory;
use Maarheeze\Compass\Collector\CollectorDrivers;
use Maarheeze\Compass\Collector\Drivers\GitHub\GitHubClient;
use Maarheeze\Compass\Collector\Drivers\GitHub\GitHubPullRequestsCollector;
use Maarheeze\Compass\Collector\SourceConfig;
use Maarheeze\Compass\Collector\SourceConfigs;

readonly class PullRequestsCollectorManager
{
    /** @var CollectorDrivers<PullRequestsCollector> */
    private CollectorDrivers $collectorDrivers;

    public function __construct(
        Container $container,
        private HttpFactory $http,
        private SourceConfigs $sourceConfigs,
    ) {
        $this->collectorDrivers = new CollectorDrivers($container);
        $this->collectorDrivers->extend(
            'github',
            function (Container $container, SourceConfig $sourceConfig): GitHubPullRequestsCollector {
                return new GitHubPullRequestsCollector(
                    new GitHubClient($this->http, $sourceConfig->setting('token')),
                    $sourceConfig->setting('organization'),
                );
            },
        );
    }

    public function collector(string $source): PullRequestsCollector
    {
        return $this->collectorDrivers->collector($this->sourceConfigs->get($source));
    }

    /**
     * @param Closure(Container, SourceConfig): PullRequestsCollector $creator
     */
    public function extend(string $driverName, Closure $creator): void
    {
        $this->collectorDrivers->extend($driverName, $creator);
    }
}
