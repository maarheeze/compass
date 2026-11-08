<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts\Types;

use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\AlertTypeGroup;

readonly class ComposerTypeMissing implements AlertType
{
    public function group(): AlertTypeGroup
    {
        return AlertTypeGroup::COMPOSER;
    }

    public function identifier(): string
    {
        return 'composer-type-missing';
    }

    public function translationKey(): string
    {
        return 'compass::alert-type.COMPOSER_TYPE_MISSING';
    }
}
