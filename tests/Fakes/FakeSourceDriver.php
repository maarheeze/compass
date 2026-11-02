<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Tests\Fakes;

use Maarheeze\Compass\Collector\GitHub\GitHubSettings;
use Maarheeze\Compass\Collector\SourceDriver;
use Webmozart\Assert\Assert;

readonly class FakeSourceDriver implements SourceDriver
{
    public function __construct(
        public GitHubSettings $settings,
    ) {
    }

    /**
     * @param array<string, mixed> $config
     */
    public static function settings(array $config): GitHubSettings
    {
        return new GitHubSettings(
            Assert::string($config['organization']),
            Assert::string($config['token']),
        );
    }
}
