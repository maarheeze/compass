<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts\Types;

use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\AlertTypeGroup;

readonly class SiteDeploymentOverdue implements AlertType
{
    public function group(): AlertTypeGroup
    {
        return AlertTypeGroup::HOSTING_PROVIDER;
    }

    public function identifier(): string
    {
        return 'site-deployment-overdue';
    }

    public function translationKey(): string
    {
        return 'compass::alert-type.SITE_DEPLOYMENT_OVERDUE';
    }
}
