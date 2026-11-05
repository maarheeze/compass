<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector;

use Webmozart\Assert\Assert;

readonly class SourceConfigs
{
    /**
     * @param array<mixed> $sources
     */
    public function __construct(
        private array $sources,
    ) {
    }

    /**
     * @return list<SourceConfig>
     */
    public function all(): array
    {
        $sourceConfigs = [];

        foreach ($this->sources as $sourceKey => $settings) {
            $sourceConfigs[] = new SourceConfig(Assert::stringNotEmpty($sourceKey), Assert::isMap($settings));
        }

        return $sourceConfigs;
    }

    public function get(string $sourceKey): SourceConfig
    {
        Assert::keyExists($this->sources, $sourceKey);

        return new SourceConfig($sourceKey, Assert::isMap($this->sources[$sourceKey]));
    }
}
