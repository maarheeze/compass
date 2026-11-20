<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts\Types;

use Maarheeze\Compass\Alerts\AlertLevel;
use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\AlertTypeGroup;

readonly class ComposerMinimumStabilityUnstable implements AlertType
{
    public function group(): AlertTypeGroup
    {
        return AlertTypeGroup::COMPOSER;
    }

    public function identifier(): string
    {
        return 'composer-minimum-stability-unstable';
    }

    public function level(): AlertLevel
    {
        return AlertLevel::WARNING;
    }

    public function translationKey(): string
    {
        return 'compass::alert-type.composer-minimum-stability-unstable';
    }
}
