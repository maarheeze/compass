<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Http;

use Illuminate\Contracts\Translation\Translator;
use Maarheeze\Compass\Collections\FilterOptionCollection;
use Maarheeze\Compass\Models\Builders\IssueBuilder;
use Maarheeze\Compass\Models\Builders\PullRequestBuilder;
use Maarheeze\Compass\View\Filter;
use Webmozart\Assert\Assert;

readonly class AssigneeFilter
{
    public const string ASSIGNEE_PARAMETER = 'assignee';
    public const string SOURCE_PARAMETER = 'source';
    public const string UNASSIGNED_PARAMETER = 'unassigned';
    public const string UNASSIGNED_VALUE = 'true';

    private function __construct(
        private Assignee|Unassigned|null $selection,
    ) {
    }

    public static function everyone(): self
    {
        return new self(null);
    }

    public static function onAssignee(Assignee $assignee): self
    {
        return new self($assignee);
    }

    public static function unassigned(): self
    {
        return new self(new Unassigned());
    }

    public static function assigneesFilter(FilterOptionCollection $assigneeFilterOptions, Translator $translator): Filter
    {
        return new Filter(
            Assert::string($translator->get('compass::assignee.assignees')),
            [self::SOURCE_PARAMETER, self::ASSIGNEE_PARAMETER, self::UNASSIGNED_PARAMETER],
            $assigneeFilterOptions,
        );
    }

    /**
     * @return array<string, string>
     */
    public static function unassignedRouteParameters(): array
    {
        return [self::UNASSIGNED_PARAMETER => self::UNASSIGNED_VALUE];
    }

    public function issues(IssueBuilder $issues): IssueBuilder
    {
        return match (true) {
            $this->selection instanceof Assignee => $issues->assignedTo($this->selection),
            $this->selection instanceof Unassigned => $issues->unassigned(),
            default => $issues,
        };
    }

    public function pullRequests(PullRequestBuilder $pullRequests): PullRequestBuilder
    {
        return match (true) {
            $this->selection instanceof Assignee => $pullRequests->assignedTo($this->selection),
            $this->selection instanceof Unassigned => $pullRequests->unassigned(),
            default => $pullRequests,
        };
    }
}
