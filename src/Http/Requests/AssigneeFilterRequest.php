<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Maarheeze\Compass\Http\Assignee;
use Maarheeze\Compass\Http\AssigneeFilter;
use Webmozart\Assert\Assert;

use function sprintf;

class AssigneeFilterRequest extends FormRequest
{
    public function assigneeFilter(): AssigneeFilter
    {
        if ($this->validated(AssigneeFilter::UNASSIGNED_PARAMETER) !== null) {
            return AssigneeFilter::unassigned();
        }

        $login = $this->validated(AssigneeFilter::ASSIGNEE_PARAMETER);

        if ($login === null) {
            return AssigneeFilter::everyone();
        }

        return AssigneeFilter::onAssignee(new Assignee(
            Assert::string($this->validated(AssigneeFilter::SOURCE_PARAMETER)),
            Assert::string($login),
        ));
    }

    /**
     * @return array<string, list<mixed>>
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
            AssigneeFilter::UNASSIGNED_PARAMETER => [
                'nullable',
                Rule::in([AssigneeFilter::UNASSIGNED_VALUE]),
            ],
        ];
    }
}
