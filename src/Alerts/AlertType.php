<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts;

enum AlertType: string
{
    case COMPOSER_LIBRARY_WITH_LOCK = 'composer-library-with-lock';
    case COMPOSER_MINIMUM_STABILITY_UNSTABLE = 'composer-minimum-stability-unstable';
    case COMPOSER_PROJECT_WITHOUT_LOCK = 'composer-project-without-lock';
    case COMPOSER_TYPE_MISSING = 'composer-type-missing';
    case COMPOSER_TYPE_UNKNOWN = 'composer-type-unknown';
    case DEPENDABOT_COMPOSER_MISSING = 'dependabot-composer-missing';
    case DEPENDABOT_NPM_MISSING = 'dependabot-npm-missing';
    case REPOSITORY_MISSING = 'repository-missing';
    case SITE_BEHIND = 'site-behind';
    case SITE_CLIENT_INCOMPATIBLE = 'site-client-incompatible';
    case SITE_CLIENT_UNAUTHORIZED = 'site-client-unauthorized';
    case SITE_DEBUG_ENABLED = 'site-debug-enabled';
    case SITE_DEPLOYMENT_OVERDUE = 'site-deployment-overdue';
    case SITE_ENDPOINT_DOWN = 'site-endpoint-down';
    case SITE_MISSING = 'site-missing';
    case SITE_NOT_IN_PRODUCTION = 'site-not-in-production';
}
