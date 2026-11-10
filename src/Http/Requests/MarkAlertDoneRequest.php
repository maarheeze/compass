<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Http\Requests;

use Closure;
use Illuminate\Validation\Validator;
use Maarheeze\Compass\Alerts\AlertDoneMarker;

class MarkAlertDoneRequest extends AlertActionRequest
{
    /**
     * @return list<Closure(Validator): void>
     */
    public function after(AlertDoneMarker $alertDoneMarker): array
    {
        return [
            function (Validator $validator) use ($alertDoneMarker): void {
                if (!$alertDoneMarker->canMarkDone($this->alert())) {
                    $this->noLongerPossible($validator);
                }
            },
        ];
    }
}
