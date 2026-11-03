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
    /** @var array<string, Closure(Container, array<string, mixed>): CollectorDriver> */
    private array $customCreators = [];

    /** @var array<string, Source> */
    private array $sources = [];

    public function __construct(
        private readonly Repository $config,
        private readonly Container $container,
    ) {
    }

    /**
     * @param Closure(Container, array<string, mixed>): CollectorDriver $creator
     */
    public function extend(string $driverName, Closure $creator): void
    {
        $this->customCreators[$driverName] = $creator;
    }

    public function source(string $key): Source
    {
        if (!array_key_exists($key, $this->sources)) {
            $this->sources[$key] = $this->makeSource($key);
        }

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
    private function makeDriver(array $sourceConfig): CollectorDriver
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

    private function makeSource(string $key): Source
    {
        $sourceConfigs = $this->config->array('compass.sources');

        Assert::keyExists($sourceConfigs, $key);

        return new Source($key, $this->makeDriver(Assert::isMap($sourceConfigs[$key])));
    }
}
