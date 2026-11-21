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
    private const string PAGE_PARAMETER = 'page';

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
        $queryWithoutFilterAndPage = Arr::except($this->request->query->all(), [...$this->filter->parameterNames, self::PAGE_PARAMETER]);

        return sprintf('%s?%s', $this->request->url(), Arr::query([...$queryWithoutFilterAndPage, ...$filterOption->queryParameters]));
    }

    public function render(): View
    {
        return $this->view->make('compass::components.filter-section');
    }
}
