<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts\Types;

use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\AlertTypeGroup;

readonly class SiteReportUnauthorized implements AlertType
{
    public function group(): AlertTypeGroup
    {
        return AlertTypeGroup::SITE_REPORT;
    }

    public function identifier(): string
    {
        return 'site-report-unauthorized';
    }

    public function translationKey(): string
    {
        return 'compass::alert-type.SITE_REPORT_UNAUTHORIZED';
    }
}
