<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector;

use Illuminate\Cache\RateLimiter;

use function sprintf;

readonly class RequestBudget
{
    public const int WINDOW_SECONDS = 60;

    public function __construct(
        private RateLimiter $rateLimiter,
        private string $source,
        private int $requestsPerMinute,
    ) {
    }

    /**
     * @throws RequestBudgetExhausted
     */
    public function reserveRequest(): void
    {
        $rateLimiterKey = sprintf('compass-request-budget:%s', $this->source);

        if ($this->rateLimiter->increment($rateLimiterKey, self::WINDOW_SECONDS) > $this->requestsPerMinute) {
            throw new RequestBudgetExhausted($this->rateLimiter->availableIn($rateLimiterKey));
        }
    }
}
