<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Http;

readonly class AssigneeFilter
{
    public const string ASSIGNEE_PARAMETER = 'assignee';
    public const string SOURCE_PARAMETER = 'source';

    public function __construct(
        public string $source,
        public string $login,
    ) {
    }

    /**
     * @return array<string, string>
     */
    public function routeParameters(): array
    {
        return [
            self::SOURCE_PARAMETER => $this->source,
            self::ASSIGNEE_PARAMETER => $this->login,
        ];
    }
}
