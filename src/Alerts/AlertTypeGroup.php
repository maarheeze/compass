<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts;

enum AlertTypeGroup: string
{
    case CODE_HOST = 'code_host';
    case COMPOSER = 'composer';
    case DEPENDABOT = 'dependabot';
    case ENDPOINT = 'endpoint';
    case HOSTING_PROVIDER = 'hosting_provider';
    case SITE_REPORT = 'site_report';

    /**
     * @return list<AlertType>
     */
    public function alertTypes(): array
    {
        return match ($this) {
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
            self::SITE_REPORT => [
                AlertType::SITE_DEBUG_ENABLED,
                AlertType::SITE_NOT_IN_PRODUCTION,
                AlertType::SITE_REPORT_INCOMPATIBLE,
                AlertType::SITE_REPORT_UNAUTHORIZED,
            ],
        };
    }
}
