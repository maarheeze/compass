<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Drivers\GitHub;

use Exception;
use Throwable;

class GitHubQueryFailed extends Exception
{
    public function __construct(
        ?Throwable $previous,
    ) {
        parent::__construct('The GitHub query failed.', 0, $previous);
    }
}
