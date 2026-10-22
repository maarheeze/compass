<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View;

enum NavigationSection
{
    case DASHBOARD;
    case ISSUES;
    case PULL_REQUESTS;
    case REPOSITORIES;
    case SITES;
}
