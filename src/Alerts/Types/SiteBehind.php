<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts\Types;

use Maarheeze\Compass\Alerts\AlertLevel;
use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\AlertTypeGroup;

readonly class SiteBehind implements AlertType
{
    public function group(): AlertTypeGroup
    {
        return AlertTypeGroup::HOSTING_PROVIDER;
    }

    public function identifier(): string
    {
        return 'site-behind';
    }

    public function level(): AlertLevel
    {
        return AlertLevel::NOTICE;
    }

    public function translationKey(): string
    {
        return 'compass::alert-type.site-behind';
    }
}
