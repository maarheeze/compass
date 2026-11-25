<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector;

use Exception;
use Illuminate\Http\Client\Response;

use function ctype_digit;

class RequestBudgetExhausted extends Exception
{
    public function __construct(
        public readonly int $secondsUntilRoom,
    ) {
        parent::__construct('The request budget of the source is exhausted.');
    }

    public static function fromRateLimitedResponse(Response $rateLimitedResponse): self
    {
        $retryAfter = $rateLimitedResponse->header('Retry-After');

        if (!ctype_digit($retryAfter)) {
            return new self(RequestBudget::WINDOW_SECONDS);
        }

        return new self((int) $retryAfter);
    }
}
