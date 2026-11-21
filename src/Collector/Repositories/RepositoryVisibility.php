<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Repositories;

use Maarheeze\Compass\View\LabelledEnum;

use function sprintf;

enum RepositoryVisibility: string implements LabelledEnum
{
    case INTERNAL = 'internal';
    case PRIVATE = 'private';
    case PUBLIC = 'public';

    /**
     * @return list<self>
     */
    public static function displayOrder(): array
    {
        return [
            self::PUBLIC,
            self::PRIVATE,
            self::INTERNAL,
        ];
    }

    public function labelKey(): string
    {
        return sprintf('compass::repository-visibility.%s', $this->name);
    }
}
