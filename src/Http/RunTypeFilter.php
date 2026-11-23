<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Http;

use Illuminate\Contracts\Translation\Translator;
use Maarheeze\Compass\Collections\FilterOptionCollection;
use Maarheeze\Compass\Collector\CollectorType;
use Maarheeze\Compass\Collector\TaskType;
use Maarheeze\Compass\Models\Builders\RunBuilder;
use Maarheeze\Compass\View\Filter;
use Webmozart\Assert\Assert;

readonly class RunTypeFilter
{
    public const string COLLECTOR_PARAMETER = 'collector';
    public const string TASK_PARAMETER = 'task';

    private function __construct(
        private CollectorType|TaskType|null $selection,
    ) {
    }

    public static function everyType(): self
    {
        return new self(null);
    }

    public static function ofCollectorType(CollectorType $collectorType): self
    {
        return new self($collectorType);
    }

    public static function ofTaskType(TaskType $taskType): self
    {
        return new self($taskType);
    }

    /**
     * @return array<string, string>
     */
    public static function collectorTypeRouteParameters(CollectorType $collectorType): array
    {
        return [self::COLLECTOR_PARAMETER => $collectorType->value];
    }

    public static function runTypesFilter(FilterOptionCollection $runTypeFilterOptions, Translator $translator): Filter
    {
        return new Filter(
            Assert::string($translator->get('compass::run.types')),
            [self::COLLECTOR_PARAMETER, self::TASK_PARAMETER],
            $runTypeFilterOptions,
        );
    }

    /**
     * @return array<string, string>
     */
    public static function taskTypeRouteParameters(TaskType $taskType): array
    {
        return [self::TASK_PARAMETER => $taskType->value];
    }

    public function runs(RunBuilder $runs): RunBuilder
    {
        return match (true) {
            $this->selection instanceof CollectorType => $runs->ofCollectorType($this->selection),
            $this->selection instanceof TaskType => $runs->ofTaskType($this->selection),
            default => $runs,
        };
    }
}
