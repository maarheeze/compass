<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts\Types;

use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\AlertTypeGroup;

readonly class ComposerLibraryWithLock implements AlertType
{
    public function group(): AlertTypeGroup
    {
        return AlertTypeGroup::COMPOSER;
    }

    public function identifier(): string
    {
        return 'composer-library-with-lock';
    }

    public function translationKey(): string
    {
        return 'compass::alert-type.COMPOSER_LIBRARY_WITH_LOCK';
    }
}
