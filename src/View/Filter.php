<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View;

use Maarheeze\Compass\Collections\FilterOptionCollection;

readonly class Filter
{
    /**
     * @param list<string> $parameterNames
     */
    public function __construct(
        public string $title,
        public array $parameterNames,
        public FilterOptionCollection $options,
    ) {
    }
}
