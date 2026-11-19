<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Maarheeze\Compass\Http\SiteFilter;
use Webmozart\Assert\Assert;

class SiteFilterRequest extends FormRequest
{
    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            SiteFilter::PHP_VERSION_PARAMETER => [
                'nullable',
                'string',
            ],
            SiteFilter::SERVER_PARAMETER => [
                'nullable',
                'string',
            ],
        ];
    }

    public function siteFilter(): SiteFilter
    {
        return new SiteFilter(
            Assert::nullOrString($this->validated(SiteFilter::SERVER_PARAMETER)),
            Assert::nullOrString($this->validated(SiteFilter::PHP_VERSION_PARAMETER)),
        );
    }
}
