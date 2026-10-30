<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts;

enum AlertTypeGroup: string
{
    case CLIENT = 'client';
    case COMPOSER = 'composer';
    case ENDPOINT = 'endpoint';
    case FORGE = 'forge';
    case GITHUB = 'github';

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
            self::COMPOSER => [
                AlertType::COMPOSER_LIBRARY_WITH_LOCK,
                AlertType::COMPOSER_PROJECT_WITHOUT_LOCK,
                AlertType::COMPOSER_TYPE_MISSING,
                AlertType::COMPOSER_TYPE_UNKNOWN,
            ],
            self::ENDPOINT => [
                AlertType::SITE_ENDPOINT_DOWN,
            ],
            self::FORGE => [
                AlertType::SITE_BEHIND,
                AlertType::SITE_DEPLOYMENT_OVERDUE,
                AlertType::SITE_MISSING,
            ],
            self::GITHUB => [
                AlertType::REPOSITORY_MISSING,
            ],
        };
    }
}
