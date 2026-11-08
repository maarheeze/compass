<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts\Types;

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

    public function translationKey(): string
    {
        return 'compass::alert-type.COMPOSER_MINIMUM_STABILITY_UNSTABLE';
    }
}
