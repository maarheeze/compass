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

        foreach ($this->sources as $source => $settings) {
            $sourceConfigs[] = new SourceConfig(Assert::stringNotEmpty($source), Assert::isMap($settings));
        }

        return $sourceConfigs;
    }

    public function get(string $source): SourceConfig
    {
        Assert::keyExists($this->sources, $source);

        return new SourceConfig($source, Assert::isMap($this->sources[$source]));
    }
}
