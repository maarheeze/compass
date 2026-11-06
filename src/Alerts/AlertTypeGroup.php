<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts;

enum AlertTypeGroup: string
{
    case CLIENT = 'client';
    case CODE_HOST = 'code_host';
    case COMPOSER = 'composer';
    case DEPENDABOT = 'dependabot';
    case ENDPOINT = 'endpoint';
    case HOSTING_PROVIDER = 'hosting_provider';

    /**
     * @return list<AlertType>
     */
    public function alertTypes(): array
    {
        return match ($this) {
            self::CLIENT => [
                AlertType::SITE_CLIENT_INCOMPATIBLE,
                AlertType::SITE_CLIENT_UNAUTHORIZED,
                AlertType::SITE_DEBUG_ENABLED,
                AlertType::SITE_NOT_IN_PRODUCTION,
            ],
            self::CODE_HOST => [
                AlertType::REPOSITORY_MISSING,
            ],
            self::COMPOSER => [
                AlertType::COMPOSER_LIBRARY_WITH_LOCK,
                AlertType::COMPOSER_MINIMUM_STABILITY_UNSTABLE,
                AlertType::COMPOSER_PROJECT_WITHOUT_LOCK,
                AlertType::COMPOSER_TYPE_MISSING,
                AlertType::COMPOSER_TYPE_UNKNOWN,
            ],
            self::DEPENDABOT => [
                AlertType::DEPENDABOT_COMPOSER_MISSING,
                AlertType::DEPENDABOT_NPM_MISSING,
            ],
            self::ENDPOINT => [
                AlertType::SITE_ENDPOINT_DOWN,
            ],
            self::HOSTING_PROVIDER => [
                AlertType::SITE_BEHIND,
                AlertType::SITE_DEPLOYMENT_OVERDUE,
                AlertType::SITE_MISSING,
            ],
        };
    }
}
