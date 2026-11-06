<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Maarheeze\Compass\Http\AssigneeFilter;
use Webmozart\Assert\Assert;

class AssigneeFilterRequest extends FormRequest
{
    public function assigneeFilter(): ?AssigneeFilter
    {
        $assignee = $this->validated('assignee');

        if ($assignee === null) {
            return null;
        }

        return new AssigneeFilter(Assert::string($this->validated('source')), Assert::string($assignee));
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'assignee' => [
                'required_with:source',
                'string',
            ],
            'source' => [
                'required_with:assignee',
                'string',
            ],
        ];
    }
}
