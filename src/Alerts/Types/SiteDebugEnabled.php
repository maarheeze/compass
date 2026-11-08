<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts\Types;

use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\AlertTypeGroup;

readonly class SiteDebugEnabled implements AlertType
{
    public function group(): AlertTypeGroup
    {
        return AlertTypeGroup::SITE_REPORT;
    }

    public function identifier(): string
    {
        return 'site-debug-enabled';
    }

    public function translationKey(): string
    {
        return 'compass::alert-type.SITE_DEBUG_ENABLED';
    }
}
