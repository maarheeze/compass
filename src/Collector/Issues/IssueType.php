<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Issues;

use Maarheeze\Compass\View\LabelledEnum;

use function sprintf;

enum IssueType: string implements LabelledEnum
{
    case BUG = 'bug';
    case FEATURE = 'feature';
    case TASK = 'task';

    /**
     * @return list<self>
     */
    public static function displayOrder(): array
    {
        return [
            self::BUG,
            self::FEATURE,
            self::TASK,
        ];
    }

    public function labelKey(): string
    {
        return sprintf('compass::issue-type.%s', $this->name);
    }
}
