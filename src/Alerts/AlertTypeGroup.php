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
                AlertType::INCOMPATIBLE_CLIENT,
                AlertType::UNAUTHORIZED_CLIENT,
                AlertType::DEBUG_ENABLED,
                AlertType::NON_PRODUCTION,
            ],
            self::COMPOSER => [
                AlertType::LIBRARY_WITH_LOCK,
                AlertType::MISSING_COMPOSER_TYPE,
                AlertType::PROJECT_WITHOUT_LOCK,
                AlertType::UNKNOWN_COMPOSER_TYPE,
            ],
            self::ENDPOINT => [
                AlertType::DOWN_SITE_ENDPOINT,
            ],
            self::FORGE => [
                AlertType::OVERDUE_DEPLOYMENT,
                AlertType::MISSING_SITE,
                AlertType::SITE_BEHIND,
            ],
            self::GITHUB => [
                AlertType::MISSING_REPOSITORY,
            ],
        };
    }
}
