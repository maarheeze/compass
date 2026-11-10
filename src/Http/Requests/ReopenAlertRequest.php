<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Http\Requests;

use Closure;
use Illuminate\Validation\Validator;
use Maarheeze\Compass\Alerts\AlertReopener;

class ReopenAlertRequest extends AlertActionRequest
{
    /**
     * @return list<Closure(Validator): void>
     */
    public function after(AlertReopener $alertReopener): array
    {
        return [
            function (Validator $validator) use ($alertReopener): void {
                if ($alertReopener->canReopen($this->alert())) {
                    return;
                }

                $this->noLongerPossible($validator);
            },
        ];
    }
}
