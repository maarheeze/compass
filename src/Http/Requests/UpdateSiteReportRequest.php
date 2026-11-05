<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\StringRule;

class UpdateSiteReportRequest extends FormRequest
{
    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'path.starts_with' => 'The path must start with a /.',
        ];
    }

    /**
     * @return array<string, list<string|StringRule>>
     */
    public function rules(): array
    {
        return [
            'key' => [
                Rule::nullable(),
                Rule::string()->max(255),
            ],
            'path' => [
                Rule::nullable(),
                Rule::string()->startsWith('/')->max(255),
            ],
        ];
    }
}
