<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Repositories;

use Maarheeze\Compass\View\LabelledEnum;

use function sprintf;

enum RepositoryVisibility: string implements LabelledEnum
{
    case INTERNAL = 'INTERNAL';
    case PRIVATE = 'PRIVATE';
    case PUBLIC = 'PUBLIC';

    public function labelKey(): string
    {
        return sprintf('compass::repository-visibility.%s', $this->name);
    }
}
