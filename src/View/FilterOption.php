<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View;

readonly class FilterOption
{
    /**
     * @param array<string, string> $queryParameters
     */
    public function __construct(
        public string $label,
        public int $count,
        public array $queryParameters,
        public ?string $avatarUrl,
    ) {
    }
}
