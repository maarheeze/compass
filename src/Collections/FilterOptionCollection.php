<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collections;

use Illuminate\Support\Collection;
use Maarheeze\Compass\View\FilterOption;

use function http_build_query;
use function strnatcasecmp;

/**
 * @extends Collection<array-key, FilterOption>
 */
class FilterOptionCollection extends Collection
{
    /**
     * @param array<string, string> $queryParameters
     */
    public function countOption(string $label, array $queryParameters, ?string $avatarUrl): void
    {
        $key = http_build_query($queryParameters);
        $filterOption = $this->get($key);

        if ($filterOption === null) {
            $this->put($key, new FilterOption($label, 1, $queryParameters, $avatarUrl));

            return;
        }

        $this->put($key, new FilterOption($label, $filterOption->count + 1, $queryParameters, $avatarUrl));
    }

    public function sortedByLabel(): self
    {
        return $this
            ->sort(static function (FilterOption $filterOption, FilterOption $otherFilterOption): int {
                return strnatcasecmp($filterOption->label, $otherFilterOption->label);
            })
            ->values();
    }

    public function sortedByLabelDescending(): self
    {
        return $this
            ->sort(static function (FilterOption $filterOption, FilterOption $otherFilterOption): int {
                return strnatcasecmp($otherFilterOption->label, $filterOption->label);
            })
            ->values();
    }
}
