<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Issues;

use Maarheeze\Compass\Collector\SourceConfig;

use function array_key_exists;

readonly class IssueTypeMapping
{
    /** @var array<string, IssueType> */
    private array $issueTypes;

    public function __construct(
        SourceConfig $sourceConfig,
    ) {
        $issueTypes = [];

        foreach ($sourceConfig->mapSetting('issue_types') as $issueTypeName => $issueTypeValue) {
            $issueTypes[$issueTypeName] = IssueType::from($issueTypeValue);
        }

        $this->issueTypes = $issueTypes;
    }

    public function issueType(?string $issueTypeName): ?IssueType
    {
        if ($issueTypeName === null || !array_key_exists($issueTypeName, $this->issueTypes)) {
            return null;
        }

        return $this->issueTypes[$issueTypeName];
    }
}
