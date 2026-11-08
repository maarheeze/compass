<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts\Types;

use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\AlertTypeGroup;

readonly class DependabotNpmMissing implements AlertType
{
    public function group(): AlertTypeGroup
    {
        return AlertTypeGroup::DEPENDABOT;
    }

    public function identifier(): string
    {
        return 'dependabot-npm-missing';
    }

    public function translationKey(): string
    {
        return 'compass::alert-type.DEPENDABOT_NPM_MISSING';
    }
}
