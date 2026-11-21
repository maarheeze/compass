<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Analyser\Composer;

use Maarheeze\Compass\View\LabelledEnum;

use function sprintf;

enum RepositoryKind: string implements LabelledEnum
{
    case LIBRARY = 'library';
    case OTHER = 'other';
    case PROJECT = 'project';

    public const array DISPLAY_ORDER = [
        self::PROJECT,
        self::LIBRARY,
        self::OTHER,
    ];

    public function composerType(): ?string
    {
        return match ($this) {
            self::LIBRARY => 'library',
            self::OTHER => null,
            self::PROJECT => 'project',
        };
    }

    public function labelKey(): string
    {
        return sprintf('compass::repository-kind.%s', $this->name);
    }
}
