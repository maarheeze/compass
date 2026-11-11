<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\SiteReports;

use Maarheeze\Compass\View\LabelledEnum;

use function sprintf;

enum SiteReportStatus: string implements LabelledEnum
{
    case INCOMPATIBLE = 'incompatible';
    case INSTALLED = 'installed';
    case NOT_INSTALLED = 'not-installed';
    case UNAUTHORIZED = 'unauthorized';

    public function labelKey(): string
    {
        return sprintf('compass::site-report-status.%s', $this->name);
    }
}
