<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\Alert;
use Webmozart\Assert\Assert;

abstract class AlertActionRequest extends FormRequest
{
    /**
     * @return array<string, never>
     */
    public function rules(): array
    {
        return [];
    }

    protected function alert(): Alert
    {
        $alert = $this->route('alert');
        Assert::isInstanceOf($alert, Alert::class);

        return $alert;
    }

    protected function getRedirectUrl(): string
    {
        return $this->redirector
            ->getUrlGenerator()
            ->route(RouteName::ALERT->value, $this->alert());
    }

    protected function noLongerPossible(Validator $validator): void
    {
        $noLongerPossibleMessage = Assert::string($validator->getTranslator()->get('compass::alert.no_longer_possible'));

        $validator->errors()->add('alert', $noLongerPossibleMessage);
    }
}
