<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Repositories;

use Closure;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Container\Container;
use InvalidArgumentException;
use Maarheeze\Compass\Collector\GitHub\GitHubClientFactory;
use Maarheeze\Compass\Collector\GitHub\GitHubRepositoriesCollector;
use Webmozart\Assert\Assert;

use function array_key_exists;

class RepositoriesCollectorManager
{
    /** @var array<string, Closure(Container, array<string, mixed>): RepositoriesCollector> */
    private array $customCreators = [];

    public function __construct(
        private readonly Repository $config,
        private readonly Container $container,
        private readonly GitHubClientFactory $gitHubClientFactory,
    ) {
    }

    public function collector(string $sourceKey): RepositoriesCollector
    {
        $sourceConfigs = $this->config->array('compass.sources');

        Assert::keyExists($sourceConfigs, $sourceKey);
        $sourceConfig = Assert::isMap($sourceConfigs[$sourceKey]);

        Assert::keyExists($sourceConfig, 'driver');
        $driverName = Assert::stringNotEmpty($sourceConfig['driver']);

        if (array_key_exists($driverName, $this->customCreators)) {
            return $this->customCreators[$driverName]($this->container, $sourceConfig);
        }

        return match ($driverName) {
            'github' => $this->createGitHubDriver($sourceConfig),
            default => throw new InvalidArgumentException(
                'A source names a driver that has no repositories collector.',
            ),
        };
    }

    /**
     * @param Closure(Container, array<string, mixed>): RepositoriesCollector $creator
     */
    public function extend(string $driverName, Closure $creator): void
    {
        $this->customCreators[$driverName] = $creator;
    }

    /**
     * @param array<string, mixed> $sourceConfig
     */
    private function createGitHubDriver(array $sourceConfig): GitHubRepositoriesCollector
    {
        Assert::keyExists($sourceConfig, 'organization');
        Assert::keyExists($sourceConfig, 'token');

        return new GitHubRepositoriesCollector(
            $this->gitHubClientFactory->make(Assert::stringNotEmpty($sourceConfig['token'])),
            Assert::stringNotEmpty($sourceConfig['organization']),
        );
    }
}
