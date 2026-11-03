<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector;

enum CollectionType: string
{
    case ISSUES = 'issues';
    case PULL_REQUESTS = 'pull_requests';
    case REPOSITORIES = 'repositories';
    case SITES = 'sites';
}
