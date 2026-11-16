<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Drivers\GitHub;

use Exception;

class GitHubSearchIncomplete extends Exception
{
    public function __construct()
    {
        parent::__construct('GitHub could not finish the search.');
    }
}
