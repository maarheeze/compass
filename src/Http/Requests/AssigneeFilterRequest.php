<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Webmozart\Assert\Assert;

class AssigneeFilterRequest extends FormRequest
{
    public function assignee(): string
    {
        return Assert::string($this->validated('assignee'));
    }

    public function isFilteredOnAssignee(): bool
    {
        return $this->has('assignee');
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

    public function source(): string
    {
        return Assert::string($this->validated('source'));
    }
}
