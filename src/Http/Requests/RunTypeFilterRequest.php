<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Maarheeze\Compass\Collector\CollectorType;
use Maarheeze\Compass\Collector\TaskType;
use Maarheeze\Compass\Http\RunTypeFilter;
use Webmozart\Assert\Assert;

class RunTypeFilterRequest extends FormRequest
{
    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            RunTypeFilter::COLLECTOR_PARAMETER => [
                'nullable',
                Rule::enum(CollectorType::class),
            ],
            RunTypeFilter::TASK_PARAMETER => [
                'nullable',
                Rule::enum(TaskType::class),
            ],
        ];
    }

    public function runTypeFilter(): RunTypeFilter
    {
        $taskTypeValue = $this->validated(RunTypeFilter::TASK_PARAMETER);

        if ($taskTypeValue !== null) {
            return RunTypeFilter::ofTaskType(TaskType::from(Assert::string($taskTypeValue)));
        }

        $collectorTypeValue = $this->validated(RunTypeFilter::COLLECTOR_PARAMETER);

        if ($collectorTypeValue === null) {
            return RunTypeFilter::everyType();
        }

        return RunTypeFilter::ofCollectorType(CollectorType::from(Assert::string($collectorTypeValue)));
    }
}
