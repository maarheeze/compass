<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts\Types;

use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\AlertTypeGroup;

readonly class ComposerProjectWithoutLock implements AlertType
{
    public function group(): AlertTypeGroup
    {
        return AlertTypeGroup::COMPOSER;
    }

    public function identifier(): string
    {
        return 'composer-project-without-lock';
    }

    public function translationKey(): string
    {
        return 'compass::alert-type.COMPOSER_PROJECT_WITHOUT_LOCK';
    }
}
