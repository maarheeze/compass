<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\GitHub;

use Exception;
use Throwable;

class GitHubQueryFailed extends Exception
{
    /**
     * @param list<string> $errorMessages
     */
    public function __construct(
        public readonly array $errorMessages,
        ?Throwable $previous,
    ) {
        parent::__construct('The GitHub query failed.', 0, $previous);
    }

    /**
     * @return array{errorMessages: list<string>}
     */
    public function context(): array
    {
        return [
            'errorMessages' => $this->errorMessages,
        ];
    }
}
