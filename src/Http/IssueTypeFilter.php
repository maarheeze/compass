<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Http;

use Illuminate\Contracts\Translation\Translator;
use Maarheeze\Compass\Collections\FilterOptionCollection;
use Maarheeze\Compass\Collector\Issues\IssueType;
use Maarheeze\Compass\Models\Builders\IssueBuilder;
use Maarheeze\Compass\View\Filter;
use Webmozart\Assert\Assert;

readonly class IssueTypeFilter
{
    public const string TYPE_PARAMETER = 'type';
    public const string UNTYPED_PARAMETER = 'untyped';
    public const string UNTYPED_VALUE = 'true';

    private function __construct(
        private IssueType|Untyped|null $selection,
    ) {
    }

    public static function everyType(): self
    {
        return new self(null);
    }

    public static function ofIssueType(IssueType $issueType): self
    {
        return new self($issueType);
    }

    public static function untyped(): self
    {
        return new self(new Untyped());
    }

    /**
     * @return array<string, string>
     */
    public static function issueTypeRouteParameters(IssueType $issueType): array
    {
        return [self::TYPE_PARAMETER => $issueType->value];
    }

    public static function issueTypesFilter(
        FilterOptionCollection $issueTypeFilterOptions,
        Translator $translator,
    ): Filter {
        return new Filter(
            Assert::string($translator->get('compass::issue.types')),
            [self::TYPE_PARAMETER, self::UNTYPED_PARAMETER],
            $issueTypeFilterOptions,
        );
    }

    /**
     * @return array<string, string>
     */
    public static function untypedRouteParameters(): array
    {
        return [self::UNTYPED_PARAMETER => self::UNTYPED_VALUE];
    }

    public function issues(IssueBuilder $issues): IssueBuilder
    {
        return match (true) {
            $this->selection instanceof IssueType => $issues->ofIssueType($this->selection),
            $this->selection instanceof Untyped => $issues->untyped(),
            default => $issues,
        };
    }
}
