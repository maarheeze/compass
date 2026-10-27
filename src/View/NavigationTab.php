<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View;

readonly class NavigationTab
{
    public function __construct(
        public string $url,
        public string $labelKey,
        public string $icon,
        public bool $isActive,
    ) {
    }
}
