<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Analyser\Composer;

use Maarheeze\Compass\Collector\Composer\ComposerType;
use Maarheeze\Compass\View\LabelledEnum;

use function sprintf;

enum RepositoryKind: string implements LabelledEnum
{
    case LIBRARY = 'library';
    case OTHER = 'other';
    case PROJECT = 'project';

    public function composerType(): ?ComposerType
    {
        return match ($this) {
            self::LIBRARY => ComposerType::LIBRARY,
            self::OTHER => null,
            self::PROJECT => ComposerType::PROJECT,
        };
    }

    /**
     * @return list<self>
     */
    public static function displayOrder(): array
    {
        return [
            self::PROJECT,
            self::LIBRARY,
            self::OTHER,
        ];
    }

    public function labelKey(): string
    {
        return sprintf('compass::repository-kind.%s', $this->name);
    }
}
