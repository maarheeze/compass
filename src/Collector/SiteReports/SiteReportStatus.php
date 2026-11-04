<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\SiteReports;

enum SiteReportStatus: string
{
    case INCOMPATIBLE = 'incompatible';
    case INSTALLED = 'installed';
    case NOT_INSTALLED = 'not-installed';
    case UNAUTHORIZED = 'unauthorized';
}
