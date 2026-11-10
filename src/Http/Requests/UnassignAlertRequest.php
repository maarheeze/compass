<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Http\Requests;

use Closure;
use Illuminate\Validation\Validator;
use Maarheeze\Compass\Alerts\AlertAssigner;

class UnassignAlertRequest extends AlertActionRequest
{
    /**
     * @return list<Closure(Validator): void>
     */
    public function after(AlertAssigner $alertAssigner): array
    {
        return [
            function (Validator $validator) use ($alertAssigner): void {
                if (!$alertAssigner->canUnassign($this->alert())) {
                    $this->noLongerPossible($validator);
                }
            },
        ];
    }
}
