<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\View\Component;
use Maarheeze\Compass\View\Filter;
use Maarheeze\Compass\View\FilterOption;

use function sprintf;

class FilterSection extends Component
{
    public readonly bool $isFiltered;
    public readonly string $unfilteredUrl;

    public function __construct(
        public readonly Filter $filter,
        private readonly Request $request,
        private readonly ViewFactory $view,
    ) {
        $this->isFiltered = $request->hasAny($filter->parameterNames);
        $this->unfilteredUrl = $request->fullUrlWithoutQuery($filter->parameterNames);
    }

    public function isSelected(FilterOption $filterOption): bool
    {
        foreach ($filterOption->queryParameters as $parameterName => $parameterValue) {
            if ($this->request->query($parameterName) !== $parameterValue) {
                return false;
            }
        }

        return true;
    }

    public function optionUrl(FilterOption $filterOption): string
    {
        $queryWithoutFilter = Arr::except($this->request->query->all(), $this->filter->parameterNames);

        return sprintf('%s?%s', $this->request->url(), Arr::query([...$queryWithoutFilter, ...$filterOption->queryParameters]));
    }

    public function render(): View
    {
        return $this->view->make('compass::components.filter-section');
    }
}
