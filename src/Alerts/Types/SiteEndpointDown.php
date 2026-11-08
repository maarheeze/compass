<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts\Types;

use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\AlertTypeGroup;

readonly class SiteEndpointDown implements AlertType
{
    public function group(): AlertTypeGroup
    {
        return AlertTypeGroup::ENDPOINT;
    }

    public function identifier(): string
    {
        return 'site-endpoint-down';
    }

    public function translationKey(): string
    {
        return 'compass::alert-type.SITE_ENDPOINT_DOWN';
    }
}
