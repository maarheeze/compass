<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Models\Builders;

use Illuminate\Contracts\Translation\Translator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Maarheeze\Compass\Collections\FilterOptionCollection;
use Maarheeze\Compass\Collector\CollectorType;
use Maarheeze\Compass\Collector\TaskType;
use Maarheeze\Compass\Http\ItemsPerPage;
use Maarheeze\Compass\Http\RunTypeFilter;
use Maarheeze\Compass\Models\Run;
use Maarheeze\Compass\View\FilterOption;
use Webmozart\Assert\Assert;

/**
 * @extends Builder<Run>
 */
class RunBuilder extends Builder
{
    /**
     * @return LengthAwarePaginator<int, Run>
     */
    public function listed(ItemsPerPage $itemsPerPage): LengthAwarePaginator
    {
        return $this
            ->with(['failures', 'subject'])
            ->latest()
            ->paginate($itemsPerPage->value);
    }

    public function ofCollectorType(CollectorType $collectorType): self
    {
        return $this->where('collector', $collectorType);
    }

    public function ofTaskType(TaskType $taskType): self
    {
        return $this->where('task', $taskType);
    }

    public function runTypeFilterOptions(Translator $translator): FilterOptionCollection
    {
        $runTypeFilterOptions = new FilterOptionCollection();

        foreach (CollectorType::displayOrder() as $collectorType) {
            $runTypeFilterOptions->push(new FilterOption(
                Assert::string($translator->get($collectorType->labelKey())),
                $this
                    ->clone()
                    ->ofCollectorType($collectorType)
                    ->count(),
                RunTypeFilter::collectorTypeRouteParameters($collectorType),
                null,
            ));
        }

        foreach (TaskType::displayOrder() as $taskType) {
            $runTypeFilterOptions->push(new FilterOption(
                Assert::string($translator->get($taskType->labelKey())),
                $this
                    ->clone()
                    ->ofTaskType($taskType)
                    ->count(),
                RunTypeFilter::taskTypeRouteParameters($taskType),
                null,
            ));
        }

        return $runTypeFilterOptions;
    }
}
