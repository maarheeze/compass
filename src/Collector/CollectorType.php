<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector;

enum CollectorType: string
{
    case ISSUES = 'issues';
    case PULL_REQUESTS = 'pull_requests';
    case REPOSITORIES = 'repositories';
    case SITE_REPORTS = 'site_reports';
    case SITES = 'sites';
}
