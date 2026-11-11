<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Analyzer\Composer;

use Maarheeze\Compass\View\LabelledEnum;

use function sprintf;

enum RepositoryKind implements LabelledEnum
{
    case LIBRARY;
    case OTHER;
    case PROJECT;

    public function labelKey(): string
    {
        return sprintf('compass::repository-kind.%s', $this->name);
    }
}
