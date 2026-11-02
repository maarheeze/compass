<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector;

use Closure;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Container\Container;
use InvalidArgumentException;
use Maarheeze\Compass\Collector\Forge\ForgeDriver;
use Maarheeze\Compass\Collector\GitHub\GitHubDriver;
use Webmozart\Assert\Assert;

use function array_key_exists;

class SourceManager
{
    /** @var array<string, Closure(Container, array<string, mixed>): SourceDriver> */
    private array $customCreators = [];

    /** @var array<string, Source>|null */
    private ?array $sources = null;

    public function __construct(
        private readonly Repository $config,
        private readonly Container $container,
    ) {
    }

    /**
     * @param Closure(Container, array<string, mixed>): SourceDriver $creator
     */
    public function extend(string $driverName, Closure $creator): void
    {
        $this->customCreators[$driverName] = $creator;
    }

    public function source(string $key): Source
    {
        if ($this->sources === null) {
            $this->sources = $this->makeSources();
        }

        Assert::keyExists($this->sources, $key);

        return $this->sources[$key];
    }

    /**
     * @param array<string, mixed> $sourceConfig
     */
    private function createForgeDriver(array $sourceConfig): ForgeDriver
    {
        Assert::keyExists($sourceConfig, 'organization');
        Assert::keyExists($sourceConfig, 'token');
        Assert::keyExists($sourceConfig, 'server_name_prefix');
        Assert::keyExists($sourceConfig, 'auto_deploy_grace_minutes');

        return new ForgeDriver(
            Assert::stringNotEmpty($sourceConfig['organization']),
            Assert::stringNotEmpty($sourceConfig['token']),
            Assert::string($sourceConfig['server_name_prefix']),
            (int) Assert::integerish($sourceConfig['auto_deploy_grace_minutes']),
        );
    }

    /**
     * @param array<string, mixed> $sourceConfig
     */
    private function createGitHubDriver(array $sourceConfig): GitHubDriver
    {
        Assert::keyExists($sourceConfig, 'organization');
        Assert::keyExists($sourceConfig, 'token');

        return new GitHubDriver(
            Assert::stringNotEmpty($sourceConfig['organization']),
            Assert::stringNotEmpty($sourceConfig['token']),
        );
    }

    /**
     * @param array<string, mixed> $sourceConfig
     */
    private function makeDriver(array $sourceConfig): SourceDriver
    {
        Assert::keyExists($sourceConfig, 'driver');

        $driverName = Assert::stringNotEmpty($sourceConfig['driver']);

        if (array_key_exists($driverName, $this->customCreators)) {
            return $this->customCreators[$driverName]($this->container, $sourceConfig);
        }

        return match ($driverName) {
            'forge' => $this->createForgeDriver($sourceConfig),
            'github' => $this->createGitHubDriver($sourceConfig),
            default => throw new InvalidArgumentException('A source names a driver that is neither built in nor extended.'),
        };
    }

    /**
     * @return array<string, Source>
     */
    private function makeSources(): array
    {
        $sources = [];

        foreach ($this->config->array('compass.sources') as $key => $sourceConfig) {
            $sourceKey = Assert::stringNotEmpty($key);
            $driver = $this->makeDriver(Assert::isMap($sourceConfig));

            $sources[$sourceKey] = new Source($sourceKey, $driver);
        }

        return $sources;
    }
}
