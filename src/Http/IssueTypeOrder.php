<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Http;

use Maarheeze\Compass\Collector\Issues\IssueType;

readonly class IssueTypeOrder
{
    /**
     * @param list<IssueType> $issueTypes
     */
    public function __construct(
        public array $issueTypes,
    ) {
    }
}
