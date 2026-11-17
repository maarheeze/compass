<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts\Types;

use Maarheeze\Compass\Alerts\AlertLevel;
use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\AlertTypeGroup;

readonly class DependabotComposerMissing implements AlertType
{
    public function group(): AlertTypeGroup
    {
        return AlertTypeGroup::DEPENDABOT;
    }

    public function identifier(): string
    {
        return 'dependabot-composer-missing';
    }

    public function level(): AlertLevel
    {
        return AlertLevel::WARNING;
    }

    public function translationKey(): string
    {
        return 'compass::alert-type.DEPENDABOT_COMPOSER_MISSING';
    }
}
