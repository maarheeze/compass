<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector;

use Illuminate\Contracts\Queue\ShouldQueue;
use Maarheeze\Uuid\Uuid;

class CollectorRegistry
{
    /**
     * @var array<string, class-string<ShouldQueue>>
     */
    private array $jobClasses = [];

    public function job(CollectorType $collectorType, Uuid $runId, string $sourceKey): ShouldQueue
    {
        return new $this->jobClasses[$collectorType->value]($runId, $sourceKey);
    }

    /**
     * @param class-string<ShouldQueue> $jobClass
     */
    public function register(CollectorType $collectorType, string $jobClass): void
    {
        $this->jobClasses[$collectorType->value] = $jobClass;
    }
}
