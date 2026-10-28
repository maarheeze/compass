<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts;

enum AlertType: string
{
    case DEBUG_ENABLED = 'debug-enabled';
    case DOWN_SITE_ENDPOINT = 'down-site-endpoint';
    case INCOMPATIBLE_CLIENT = 'incompatible-client';
    case LIBRARY_WITH_LOCK = 'library-with-lock';
    case MISSING_COMPOSER_TYPE = 'missing-composer-type';
    case MISSING_REPOSITORY = 'missing-repository';
    case MISSING_SITE = 'missing-site';
    case NON_PRODUCTION = 'non-production';
    case OVERDUE_DEPLOYMENT = 'overdue-deployment';
    case PROJECT_WITHOUT_LOCK = 'project-without-lock';
    case SITE_BEHIND = 'site-behind';
    case UNAUTHORIZED_CLIENT = 'unauthorized-client';
    case UNKNOWN_COMPOSER_TYPE = 'unknown-composer-type';
}
