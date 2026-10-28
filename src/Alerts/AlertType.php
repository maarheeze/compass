<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts;

enum AlertType: string
{
    case COMPOSER_WARNING = 'composer-warning';
    case DEBUG_ENABLED = 'debug-enabled';
    case DOWN_SITE_ENDPOINT = 'down-site-endpoint';
    case INCOMPATIBLE_CLIENT = 'incompatible-client';
    case MISSING_REPOSITORY = 'missing-repository';
    case MISSING_SITE = 'missing-site';
    case NON_PRODUCTION = 'non-production';
    case SITE_BEHIND = 'site-behind';
    case UNAUTHORIZED_CLIENT = 'unauthorized-client';
}
