<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Maarheeze\Compass\Collector\Issues\IssueType;
use Maarheeze\Compass\Http\IssueTypeFilter;
use Webmozart\Assert\Assert;

class IssueTypeFilterRequest extends FormRequest
{
    public function issueTypeFilter(): IssueTypeFilter
    {
        if ($this->validated(IssueTypeFilter::UNTYPED_PARAMETER) !== null) {
            return IssueTypeFilter::untyped();
        }

        $issueTypeValue = $this->validated(IssueTypeFilter::TYPE_PARAMETER);

        if ($issueTypeValue === null) {
            return IssueTypeFilter::everyType();
        }

        return IssueTypeFilter::ofIssueType(IssueType::from(Assert::string($issueTypeValue)));
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            IssueTypeFilter::TYPE_PARAMETER => [
                'nullable',
                Rule::enum(IssueType::class),
            ],
            IssueTypeFilter::UNTYPED_PARAMETER => [
                'nullable',
                Rule::in([IssueTypeFilter::UNTYPED_VALUE]),
            ],
        ];
    }
}
