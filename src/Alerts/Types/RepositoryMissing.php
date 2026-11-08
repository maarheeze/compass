<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts\Types;

use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\AlertTypeGroup;

readonly class RepositoryMissing implements AlertType
{
    public function group(): AlertTypeGroup
    {
        return AlertTypeGroup::CODE_HOST;
    }

    public function identifier(): string
    {
        return 'repository-missing';
    }

    public function translationKey(): string
    {
        return 'compass::alert-type.REPOSITORY_MISSING';
    }
}
