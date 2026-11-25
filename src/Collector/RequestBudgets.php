<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector;

use Illuminate\Cache\RateLimiter;

readonly class RequestBudgets
{
    public function __construct(
        private RateLimiter $rateLimiter,
    ) {
    }

    public function forSource(SourceConfig $sourceConfig): RequestBudget
    {
        return new RequestBudget(
            $this->rateLimiter,
            $sourceConfig->key,
            $sourceConfig->integerSetting('requests_per_minute'),
        );
    }
}
