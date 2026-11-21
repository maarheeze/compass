<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\StringRule;
use Illuminate\Validation\Rules\Unique;
use Maarheeze\Compass\Models\Site;
use Webmozart\Assert\Assert;

class UpdateSiteRequest extends FormRequest
{
    /**
     * @return array<string, list<string|StringRule|Unique>>
     */
    public function rules(): array
    {
        return [
            'key' => [
                Rule::required(),
                Rule::string()->min(8)->max(255),
                Rule::unique(Site::class, 'key')->ignore($this->site()),
            ],
        ];
    }

    private function site(): Site
    {
        $site = $this->route('site');
        Assert::isInstanceOf($site, Site::class);

        return $site;
    }
}
