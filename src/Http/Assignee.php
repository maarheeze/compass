<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Http;

readonly class Assignee
{
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
            AssigneeFilter::SOURCE_PARAMETER => $this->source,
            AssigneeFilter::ASSIGNEE_PARAMETER => $this->login,
        ];
    }
}
