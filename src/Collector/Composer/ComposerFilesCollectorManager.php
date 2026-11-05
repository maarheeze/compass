<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Composer;

use Closure;
use Illuminate\Contracts\Container\Container;
use InvalidArgumentException;
use Maarheeze\Compass\Collector\GitHub\GitHubClientFactory;
use Maarheeze\Compass\Collector\GitHub\GitHubComposerFilesCollector;
use Maarheeze\Compass\Collector\SourceConfig;
use Maarheeze\Compass\Collector\SourceConfigs;

use function array_key_exists;

class ComposerFilesCollectorManager
{
    /** @var array<string, Closure(Container, SourceConfig): ComposerFilesCollector> */
    private array $customCreators = [];

    public function __construct(
        private readonly Container $container,
        private readonly GitHubClientFactory $gitHubClientFactory,
        private readonly SourceConfigs $sourceConfigs,
    ) {
    }

    public function collector(string $sourceKey): ComposerFilesCollector
    {
        $sourceConfig = $this->sourceConfigs->get($sourceKey);
        $driverName = $sourceConfig->driver();

        if (array_key_exists($driverName, $this->customCreators)) {
            return $this->customCreators[$driverName]($this->container, $sourceConfig);
        }

        return match ($driverName) {
            'github' => $this->createGitHubDriver($sourceConfig),
            default => throw new InvalidArgumentException('unknown composer files driver'),
        };
    }

    /**
     * @param Closure(Container, SourceConfig): ComposerFilesCollector $creator
     */
    public function extend(string $driverName, Closure $creator): void
    {
        $this->customCreators[$driverName] = $creator;
    }

    private function createGitHubDriver(SourceConfig $sourceConfig): GitHubComposerFilesCollector
    {
        return new GitHubComposerFilesCollector(
            $this->gitHubClientFactory->make($sourceConfig->setting('token')),
            $sourceConfig->setting('organization'),
        );
    }
}
