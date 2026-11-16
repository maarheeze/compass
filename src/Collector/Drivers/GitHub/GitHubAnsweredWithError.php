<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Drivers\GitHub;

use Exception;
use Throwable;

class GitHubAnsweredWithError extends Exception
{
    public function __construct(
        string $errorMessage,
        ?Throwable $previous,
    ) {
        parent::__construct($errorMessage, 0, $previous);
    }
}
