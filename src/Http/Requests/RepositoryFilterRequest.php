<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Maarheeze\Compass\Analyser\Composer\RepositoryKind;
use Maarheeze\Compass\Collector\Repositories\RepositoryVisibility;
use Maarheeze\Compass\Http\RepositoryFilter;
use Webmozart\Assert\Assert;

class RepositoryFilterRequest extends FormRequest
{
    public function repositoryFilter(): RepositoryFilter
    {
        return new RepositoryFilter($this->kind(), $this->visibility());
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            RepositoryFilter::KIND_PARAMETER => [
                'nullable',
                Rule::enum(RepositoryKind::class),
            ],
            RepositoryFilter::VISIBILITY_PARAMETER => [
                'nullable',
                Rule::enum(RepositoryVisibility::class),
            ],
        ];
    }

    private function kind(): ?RepositoryKind
    {
        $kindValue = $this->validated(RepositoryFilter::KIND_PARAMETER);

        if ($kindValue === null) {
            return null;
        }

        return RepositoryKind::from(Assert::string($kindValue));
    }

    private function visibility(): ?RepositoryVisibility
    {
        $visibilityValue = $this->validated(RepositoryFilter::VISIBILITY_PARAMETER);

        if ($visibilityValue === null) {
            return null;
        }

        return RepositoryVisibility::from(Assert::string($visibilityValue));
    }
}
