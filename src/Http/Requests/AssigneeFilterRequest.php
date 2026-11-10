<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Maarheeze\Compass\Http\AssigneeFilter;
use Webmozart\Assert\Assert;

use function sprintf;

class AssigneeFilterRequest extends FormRequest
{
    public function assigneeFilter(): ?AssigneeFilter
    {
        $assignee = $this->validated(AssigneeFilter::ASSIGNEE_PARAMETER);

        if ($assignee === null) {
            return null;
        }

        return new AssigneeFilter(
            Assert::string($this->validated(AssigneeFilter::SOURCE_PARAMETER)),
            Assert::string($assignee),
        );
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            AssigneeFilter::ASSIGNEE_PARAMETER => [
                sprintf('required_with:%s', AssigneeFilter::SOURCE_PARAMETER),
                'string',
            ],
            AssigneeFilter::SOURCE_PARAMETER => [
                sprintf('required_with:%s', AssigneeFilter::ASSIGNEE_PARAMETER),
                'string',
            ],
        ];
    }
}
